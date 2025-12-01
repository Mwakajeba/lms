<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartAccount extends Model
{
    use HasFactory,LogsActivity;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chart_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_class_group_id',
        'account_code',
        'account_name',
        'has_cash_flow',
        'has_equity',
        'cash_flow_category_id',
        'equity_category_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'has_cash_flow' => 'boolean',
        'has_equity' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the account class group that owns the chart account.
     */
    public function accountClassGroup(): BelongsTo
    {
        return $this->belongsTo(AccountClassGroup::class, 'account_class_group_id');
    }

    /**
     * Get the account class through the account class group.
     */
    public function accountClass()
    {
        if ($this->accountClassGroup && $this->accountClassGroup->accountClass) {
            return $this->accountClassGroup->accountClass;
        }
        return null;
    }

    /**
     * Get the cash flow category for this chart account.
     */
    public function cashFlowCategory(): BelongsTo
    {
        return $this->belongsTo(CashFlowCategory::class, 'cash_flow_category_id');
    }

    /**
     * Get the equity category for this chart account.
     */
    public function equityCategory(): BelongsTo
    {
        return $this->belongsTo(EquityCategory::class, 'equity_category_id');
    }

    /**
     * Get the bank accounts for this chart account.
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class, 'chart_account_id');
    }

    /**
     * Get the GL transactions for this chart account.
     */
    public function glTransactions(): HasMany
    {
        return $this->hasMany(GlTransaction::class, 'chart_account_id');
    }
}
