<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'reference',
        'reference_type',
        'reference_number',
        'amount',
        'date',
        'description',
        'user_id',
        'attachment',
        'bank_account_id',
        'payee_type',
        'payee_id',
        'payee_name',
        'customer_id',
        'supplier_id',
        'branch_id',
        'approved',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'datetime',
        'approved' => 'boolean',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function receiptItems()
    {
        return $this->hasMany(ReceiptItem::class);
    }

    public function glTransactions()
    {
        return $this->hasMany(GlTransaction::class, 'transaction_id')
            ->where('transaction_type', 'receipt');
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'reference');
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

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('payee_type', 'customer')->where('payee_id', $customerId);
    }

    public function scopeByBankAccount($query, $bankAccountId)
    {
        return $query->where('bank_account_id', $bankAccountId);
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedDateAttribute()
    {
        return $this->date->format('M d, Y');
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
        return $this->receiptItems->sum('amount');
    }

    public function getPayeeNameAttribute($value)
    {
        if ($this->payee_type === 'customer' && $this->customer) {
            return $this->customer->name;
        }
        return $value;
    }
}
