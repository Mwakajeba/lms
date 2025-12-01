<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Helpers\HashIdHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'date',
        'due_date',
        'supplier_id',
        'note',
        'credit_account',
        'paid',
        'user_id',
        'branch_id',
        'company_id',
        'reference',
        'total_amount',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'paid' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creditAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'credit_account');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function billItems(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'reference')
            ->where('reference_type', 'Bill');
    }

    public function glTransactions(): HasMany
    {
        return $this->hasMany(GlTransaction::class, 'transaction_id')
            ->where('transaction_type', 'bill');
    }

    // Scopes
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereRaw('paid < total_amount OR paid IS NULL');
    }

    public function scopePaid($query)
    {
        return $query->whereRaw('paid >= total_amount');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->toDateString())
            ->whereRaw('paid < total_amount OR paid IS NULL');
    }

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('Y-m-d') : null;
    }

    public function getFormattedDueDateAttribute()
    {
        return $this->due_date ? $this->due_date->format('Y-m-d') : null;
    }

    public function getFormattedTotalAmountAttribute()
    {
        return number_format($this->total_amount, 2);
    }

    public function getFormattedPaidAttribute()
    {
        return number_format($this->paid ?? 0, 2);
    }

    public function getBalanceAttribute()
    {
        return $this->total_amount - ($this->paid ?? 0);
    }

    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 2);
    }

    public function getStatusAttribute()
    {
        if ($this->balance <= 0) {
            return 'paid';
        } elseif ($this->due_date && $this->due_date < now()->toDateString()) {
            return 'overdue';
        } else {
            return 'pending';
        }
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'paid' => '<span class="badge bg-success">Paid</span>',
            'overdue' => '<span class="badge bg-danger">Overdue</span>',
            'pending' => '<span class="badge bg-warning">Pending</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    // Methods
    public function isPaid()
    {
        return $this->balance <= 0;
    }

    public function isOverdue()
    {
        return $this->due_date && $this->due_date < now()->toDateString() && $this->balance > 0;
    }

    public function isPending()
    {
        return !$this->isPaid() && !$this->isOverdue();
    }

    public function updatePaidAmount()
    {
        $totalPaid = $this->payments()->sum('amount');
        $this->update(['paid' => $totalPaid]);
    }

    /**
     * Resolve model binding using hash ID.
     */
    public function resolveRouteBinding($value, $field = null)
    {
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
}
