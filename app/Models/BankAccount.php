<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory,LogsActivity;

    protected $table = 'bank_accounts';
    protected $fillable = ['chart_account_id', 'name', 'account_number'];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the chart account that owns the bank account.
     */
    public function chartAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'chart_account_id');
    }

    /**
     * Get the GL transactions for this bank account.
     */
    public function glTransactions(): HasMany
    {
        return $this->hasMany(GlTransaction::class, 'chart_account_id', 'chart_account_id');
    }

    public function repaymente(){
        return $this->hasMany(Repayment::class,'bank_account_id');
    }

    /**
     * Calculate the current balance of the bank account.
     */
    public function getBalanceAttribute()
    {
        if (!$this->chart_account_id) {
            return 0;
        }

        $debits = $this->glTransactions()
            ->where('nature', 'debit')
            ->sum('amount');

        $credits = $this->glTransactions()
            ->where('nature', 'credit')
            ->sum('amount');

        // For bank accounts, debits increase balance, credits decrease balance
        // This is because bank accounts are asset accounts (debit to increase, credit to decrease)
        return $debits - $credits;
    }

    /**
     * Get formatted balance.
     */
    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 2);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Get the branches assigned to this bank account.
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'bank_branch', 'bank_account_id', 'branch_id')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include bank accounts assigned to a specific branch.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $branchId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForBranch($query, $branchId = null)
    {
        if ($branchId) {
            return $query->whereHas('branches', function ($q) use ($branchId) {
                $q->where('branches.id', $branchId);
            });
        }
        
        return $query;
    }
}