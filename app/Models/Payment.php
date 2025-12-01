<?php

namespace App\Models;

use App\Helpers\HashIdHelper;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'reference',
        'reference_type',
        'reference_number',
        'amount',
        'date',
        'description',
        'attachment',
        'bank_account_id',
        'payee_type',
        'payee_id',
        'payee_name',
        'customer_id',
        'supplier_id',
        'branch_id',
        'user_id',
        'approved',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'datetime',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the payee based on payee_type and payee_id
     */
    public function payee()
    {
        if ($this->payee_type === 'customer') {
            return $this->belongsTo(Customer::class, 'payee_id');
        } elseif ($this->payee_type === 'supplier') {
            return $this->belongsTo(Supplier::class, 'payee_id');
        }
        return null;
    }

    /**
     * Get the payee display name
     */
    public function getPayeeDisplayNameAttribute()
    {
        if ($this->payee_type === 'customer' && $this->customer) {
            return $this->customer->name;
        } elseif ($this->payee_type === 'supplier' && $this->supplier) {
            return $this->supplier->name;
        } elseif ($this->payee_type === 'other') {
            return $this->payee_name ?? 'N/A';
        }
        return 'N/A';
    }



    public function paymentItems()
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function glTransactions()
    {
        return $this->hasMany(GlTransaction::class, 'transaction_id')
            ->where('transaction_type', 'payment');
    }

    public function approvals()
    {
        return $this->hasMany(PaymentVoucherApproval::class);
    }

    public function pendingApprovals()
    {
        return $this->hasMany(PaymentVoucherApproval::class)->pending();
    }

    public function currentApproval()
    {
        return $this->hasMany(PaymentVoucherApproval::class)->pending()->orderBy('approval_level')->first();
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('approved', false);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByReference($query, $reference)
    {
        return $query->where('reference', 'like', "%{$reference}%");
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('M d, Y') : 'N/A';
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->approved) {
            return '<span class="badge bg-success">Approved</span>';
        }
        return '<span class="badge bg-warning">Pending</span>';
    }

    public function getTotalAmountAttribute()
    {
        return $this->paymentItems->sum('amount');
    }

    public function getAttachmentNameAttribute()
    {
        if (!$this->attachment) {
            return null;
        }
        return basename($this->attachment);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'hash_id';
    }

    /**
     * Resolve the model instance for the given hash ID.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // If field is hash_id, decode the hash ID
        if ($field === 'hash_id' || $field === null) {
            $id = HashIdHelper::decode($value);
            if ($id !== null) {
                return $this->findOrFail($id);
            }
        }
        
        // If not a hash ID, try as regular ID
        return $this->findOrFail($value);
    }

    /**
     * Get the hash ID for this model.
     */
    public function getHashIdAttribute()
    {
        return HashIdHelper::encode($this->id);
    }

    /**
     * Get the hash ID for routing.
     */
    public function getRouteKey()
    {
        return HashIdHelper::encode($this->id);
    }

    /**
     * Check if payment requires approval.
     */
    public function requiresApproval()
    {
        // Only manual payment vouchers require approval
        if ($this->reference_type !== 'manual') {
            return false;
        }

        $settings = PaymentVoucherApprovalSetting::where('company_id', $this->user->company_id)->first();
        
        if (!$settings) {
            return false; // No approval settings configured
        }

        return $settings->getRequiredApprovalLevel($this->amount) > 0;
    }

    /**
     * Get the required approval level for this payment.
     */
    public function getRequiredApprovalLevel()
    {
        // Only manual payment vouchers require approval
        if ($this->reference_type !== 'manual') {
            return 0; // No approval required
        }

        $settings = PaymentVoucherApprovalSetting::where('company_id', $this->user->company_id)->first();
        
        if (!$settings) {
            return 0; // No approval required
        }

        return $settings->getRequiredApprovalLevel($this->amount);
    }

    /**
     * Initialize approval workflow for this payment.
     */
    public function initializeApprovalWorkflow()
    {
        // Only manual payment vouchers require approval
        if ($this->reference_type !== 'manual') {
            // Auto-approve non-manual payments
            $this->update([
                'approved' => true,
                'approved_by' => $this->user_id,
                'approved_at' => now(),
            ]);
            return;
        }

        $settings = PaymentVoucherApprovalSetting::where('company_id', $this->user->company_id)->first();
        
        if (!$settings) {
            // No approval settings configured - auto-approve all payments
            $this->update([
                'approved' => true,
                'approved_by' => $this->user_id,
                'approved_at' => now(),
            ]);
            
            // Create GL transactions for auto-approved payments
            $this->createGlTransactions();
            return;
        }

        $requiredLevel = $settings->getRequiredApprovalLevel($this->amount);
        
        if ($requiredLevel === 0) {
            // Auto-approve
            $this->update([
                'approved' => true,
                'approved_by' => $this->user_id,
                'approved_at' => now(),
            ]);
            
            // Create GL transactions for auto-approved payments
            $this->createGlTransactions();
            return;
        }

        // Create approval records for each level
        for ($level = 1; $level <= $requiredLevel; $level++) {
            $approvalType = $settings->{"level{$level}_approval_type"};
            $approvers = $settings->{"level{$level}_approvers"} ?? [];

            if ($approvalType === 'role') {
                foreach ($approvers as $roleName) {
                    $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
                    if ($role) {
                        PaymentVoucherApproval::create([
                            'payment_id' => $this->id,
                            'approval_level' => $level,
                            'approver_type' => 'role',
                            'approver_name' => $role->name,
                            'status' => 'pending',
                        ]);
                    }
                }
            } elseif ($approvalType === 'user') {
                foreach ($approvers as $userId) {
                    // Ensure userId is an integer
                    $userId = (int) $userId;
                    $user = User::find($userId);
                    if ($user) {
                        PaymentVoucherApproval::create([
                            'payment_id' => $this->id,
                            'approval_level' => $level,
                            'approver_id' => $user->id,
                            'approver_type' => 'user',
                            'approver_name' => $user->name,
                            'status' => 'pending',
                        ]);
                    }
                }
            }
        }

        // Update payment status
        $this->update([
            'approved' => false,
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Check if payment is fully approved.
     */
    public function isFullyApproved()
    {
        $requiredLevel = $this->getRequiredApprovalLevel();
        
        if ($requiredLevel === 0) {
            return $this->approved;
        }

        // Check if the required approval level is approved
        $requiredLevelApproved = $this->approvals()
            ->where('approval_level', $requiredLevel)
            ->where('status', 'approved')
            ->exists();
            
        return $requiredLevelApproved;
    }

    /**
     * Check if payment is rejected.
     */
    public function isRejected()
    {
        return $this->approvals()->rejected()->exists();
    }

    /**
     * Get approval status for display.
     */
    public function getApprovalStatusAttribute()
    {
        if ($this->isRejected()) {
            return 'rejected';
        }

        // If payment is already approved (either auto-approved or manually approved), keep it approved
        if ($this->approved) {
            return 'approved';
        }

        // Only check approval requirements for unapproved payments
        if ($this->requiresApproval()) {
            return 'pending';
        }

        return 'approved'; // Auto-approved if no approval required
    }

    /**
     * Get approval status badge for display.
     */
    public function getApprovalStatusBadgeAttribute()
    {
        $status = $this->approval_status;
        
        switch ($status) {
            case 'approved':
                return '<span class="badge bg-success">Approved</span>';
            case 'rejected':
                return '<span class="badge bg-danger">Rejected</span>';
            case 'pending':
                return '<span class="badge bg-warning">Pending Approval</span>';
            default:
                return '<span class="badge bg-secondary">Unknown</span>';
        }
    }

    /**
     * Get reference type badge for display.
     */
    public function getReferenceTypeBadgeAttribute()
    {
        if ($this->reference_type === 'manual') {
            return '<span class="badge bg-primary">Manual Payment Voucher</span>';
        } else {
            return '<span class="badge bg-secondary">' . ucfirst(str_replace(' ', ' ', $this->reference_type)) . '</span>';
        }
    }

    /**
     * Create GL transactions for this payment voucher.
     */
    public function createGlTransactions()
    {
        // Check if GL transactions already exist to avoid duplicates
        if ($this->glTransactions()->exists()) {
            return;
        }

        $this->loadMissing(['bankAccount', 'paymentItems']);

        if (!$this->bankAccount || !$this->paymentItems->count()) {
            return;
        }

        $bankAccount = $this->bankAccount;
        $date = $this->date;
        $description = $this->description ?: "Payment voucher {$this->reference}";
        
        // Prepare description for GL transactions
        $glDescription = $description;
        if ($this->payee_type === 'other' && $this->payee_name) {
            $glDescription = $this->payee_name . ' - ' . $glDescription;
        }
        
        $branchId = $this->branch_id;
        $userId = $this->user_id;

        // Credit bank account with total amount
        GlTransaction::create([
            'chart_account_id' => $bankAccount->chart_account_id,
            'customer_id' => $this->customer_id,
            'supplier_id' => $this->supplier_id,
            'amount' => $this->amount,
            'nature' => 'credit',
            'transaction_id' => $this->id,
            'transaction_type' => 'payment',
            'date' => $date,
            'description' => $glDescription,
            'branch_id' => $branchId,
            'user_id' => $userId,
        ]);

        // Debit each expense line
        foreach ($this->paymentItems as $item) {
            $itemDescription = $item->description ?: $description;
            if ($this->payee_type === 'other' && $this->payee_name) {
                $itemDescription = $this->payee_name . ' - ' . $itemDescription;
            }
            
            GlTransaction::create([
                'chart_account_id' => $item->chart_account_id,
                'customer_id' => $this->customer_id,
                'supplier_id' => $this->supplier_id,
                'amount' => $item->amount,
                'nature' => 'debit',
                'transaction_id' => $this->id,
                'transaction_type' => 'payment',
                'date' => $date,
                'description' => $itemDescription,
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]);
        }
    }
}
