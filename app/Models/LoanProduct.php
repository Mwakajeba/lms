<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanProduct extends Model
{
    use LogsActivity;
    protected $fillable = [
        'name',
        'product_type',
        'minimum_interest_rate',
        'maximum_interest_rate',
        'interest_cycle',
        'interest_method',
        'minimum_principal',
        'maximum_principal',
        'minimum_period',
        'maximum_period',
        'grace_period', // Added grace period
        'maximum_number_of_loans', // Added maximum number of loans
        'top_up_type',
        'top_up_type_value',
        'has_cash_collateral',
        'cash_collateral_type',
        'cash_collateral_value_type',
        'cash_collateral_value',
        'has_approval_levels',
        'approval_levels',
        'principal_receivable_account_id',
        'interest_receivable_account_id',
        'interest_revenue_account_id',
        'direct_writeoff_account_id',
        'provision_writeoff_account_id',
        'income_provision_account_id',
        'fees_ids',
        'penalty_ids',
        'repayment_order',
        'is_active',
        'penalt_deduction_criteria',
        'allow_push_to_ess',
    ];

    public function incomeProvisionAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'income_provision_account_id');
    }
    public function directWriteoffAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'direct_writeoff_account_id');
    }

    public function provisionWriteoffAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'provision_writeoff_account_id');
    }

    protected $casts = [
        'minimum_interest_rate' => 'decimal:15',
        'maximum_interest_rate' => 'decimal:15',
        'minimum_principal' => 'decimal:15',
        'maximum_principal' => 'decimal:15',
        'top_up_type_value' => 'decimal:15',
        'has_cash_collateral' => 'boolean',
        'cash_collateral_value' => 'decimal:15',
        'has_approval_levels' => 'boolean',
        'minimum_period' => 'integer',
        'maximum_period' => 'integer',
        'maximum_number_of_loans' => 'integer',
        'fees_ids' => 'array',
        'penalty_ids' => 'array',
        'is_active' => 'boolean',
        'allow_push_to_ess' => 'boolean',
    ];


    public static function frequencies()
    {
        return [
            'Daily' => 365,
            'Weekly' => 52,
            'Monthly' => 12,
            'Quarterly' => 4,
            'Semi Annually' => 2,
            'Annually' => 1,
        ];
    }

    /**
     * Get the principal receivable account for this loan product
     */
    public function principalReceivableAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'principal_receivable_account_id');
    }

    /**
     * Get the interest receivable account for this loan product
     */
    public function interestReceivableAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'interest_receivable_account_id');
    }

    /**
     * Get the interest revenue account for this loan product
     */
    public function interestRevenueAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'interest_revenue_account_id');
    }

    /**
     * Get the fees associated with this loan product
     */
    public function fees()
    {
        return $this->belongsToMany(Fee::class, null, null, null, 'fees_ids');
    }

    /**
     * Check if this loan product requires collateral
     */
    public function requiresCollateral(): bool
    {
        return $this->has_cash_collateral && !empty($this->cash_collateral_value);
    }

    /**
     * Calculate the required collateral amount for a given loan amount
     */
    public function calculateRequiredCollateral(float $loanAmount): float
    {
        if (!$this->requiresCollateral()) {
            return 0;
        }

        if ($this->cash_collateral_value_type === 'percentage') {
            // Calculate percentage of loan amount
            return ($loanAmount * $this->cash_collateral_value) / 100;
        } else {
            // Fixed amount
            return (float) $this->cash_collateral_value;
        }
    }

    /**
     * Get the collateral type description
     */
    public function getCollateralTypeDescription(): string
    {
        if (!$this->has_cash_collateral) {
            return 'No collateral required';
        }

        $type = $this->cash_collateral_type ?? 'Cash Deposit';
        $value = $this->cash_collateral_value ?? 0;
        $valueType = $this->cash_collateral_value_type ?? 'fixed_amount';

        if ($valueType === 'percentage') {
            return "{$type} ({$value}% of loan amount)";
        } else {
            return "{$type} (Fixed: TZS " . number_format($value, 2) . ")";
        }
    }

    /**
     * Check if the loan product is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get the effective interest rate for a given amount
     */
    public function getEffectiveInterestRate(float $amount): float
    {
        // If amount is within minimum range, use minimum rate
        if ($amount <= $this->minimum_principal) {
            return (float) $this->minimum_interest_rate;
        }

        // If amount is at maximum, use maximum rate
        if ($amount >= $this->maximum_principal) {
            return (float) $this->maximum_interest_rate;
        }

        // For amounts between min and max, you could implement a sliding scale
        // For now, we'll use the minimum rate
        return (float) $this->minimum_interest_rate;
    }

    /**
     * Check if a loan amount is within the product limits
     */
    public function isAmountWithinLimits(float $amount): bool
    {
        return $amount >= $this->minimum_principal && $amount <= $this->maximum_principal;
    }

    /**
     * Check if a period is within the product limits
     */
    public function isPeriodWithinLimits(int $period): bool
    {
        return $period >= $this->minimum_period && $period <= $this->maximum_period;
    }

    /**
     * Check if customer has reached maximum number of loans for this product
     */
    public function hasReachedMaxLoans(int $customerId): bool
    {
        // If no maximum is set, allow unlimited loans
        if (is_null($this->maximum_number_of_loans)) {
            return false;
        }

        // Count active loans for this customer and product
        $activeLoansCount = \App\Models\Loan::where('customer_id', $customerId)
            ->where('product_id', $this->id)
            ->where('status', 'active')
            ->count();

        return $activeLoansCount >= $this->maximum_number_of_loans;
    }

    /**
     * Get the remaining number of loans a customer can have for this product
     */
    public function getRemainingLoans(int $customerId): int
    {
        // If no maximum is set, return -1 (unlimited)
        if (is_null($this->maximum_number_of_loans)) {
            return -1;
        }

        // Count active loans for this customer and product
        $activeLoansCount = \App\Models\Loan::where('customer_id', $customerId)
            ->where('product_id', $this->id)
            ->where('status', 'active')
            ->count();

        return max(0, $this->maximum_number_of_loans - $activeLoansCount);
    }

    /**
     * Get loan statistics for a specific customer and this product
     */
    public function getCustomerLoanStats(int $customerId): array
    {
        $activeLoansCount = \App\Models\Loan::where('customer_id', $customerId)
            ->where('product_id', $this->id)
            ->where('status', 'active')
            ->count();

        $totalLoansCount = \App\Models\Loan::where('customer_id', $customerId)
            ->where('product_id', $this->id)
            ->count();

        return [
            'active_loans' => $activeLoansCount,
            'total_loans' => $totalLoansCount,
            'max_loans' => $this->maximum_number_of_loans,
            'remaining_loans' => $this->getRemainingLoans($customerId),
            'has_limit' => !is_null($this->maximum_number_of_loans),
            'can_create_loan' => !$this->hasReachedMaxLoans($customerId)
        ];
    }

    /**
     * Calculate the top-up amount for a given loan amount
     */
    public function topupAmount(float $loanAmount): float
    {
        if (!$this->top_up_type || $this->top_up_type === 'none') {
            return 0;
        }

        switch ($this->top_up_type) {
            case 'percentage':
                return ($loanAmount * $this->top_up_type_value) / 100;
            case 'fixed_amount':
                return (float) $this->top_up_type_value;
            case 'number_of_installments':
                // This would need more complex logic based on installment calculations
                // For now, return 0 as this type needs additional parameters
                return 0;
            default:
                return 0;
        }
    }

    /**
     * Get the first penalty associated with this loan product
     */
    public function getPenaltyAttribute()
    {
        if (!$this->penalty_ids || empty($this->penalty_ids)) {
            return null;
        }

        // Get the first penalty ID from the array
        $penaltyId = is_array($this->penalty_ids) ? $this->penalty_ids[0] : $this->penalty_ids;

        return Penalty::find($penaltyId);
    }

    /**
     * Get all penalties associated with this loan product
     */
    public function penalties()
    {
        if (!$this->penalty_ids || empty($this->penalty_ids)) {
            return collect();
        }

        $penaltyIds = is_array($this->penalty_ids) ? $this->penalty_ids : [$this->penalty_ids];

        return Penalty::whereIn('id', $penaltyIds)->get();
    }

    /**
     * Get all fees associated with this loan product
     */
    public function getFeesAttribute()
    {
        if (!$this->fees_ids || empty($this->fees_ids)) {
            return collect();
        }

        $feeIds = is_array($this->fees_ids) ? $this->fees_ids : [$this->fees_ids];

        return Fee::whereIn('id', $feeIds)->get();
    }

    /**
     * Get all loans using this product
     */
    public function loans()
    {
        return $this->hasMany(Loan::class, 'product_id');
    }

    public function getApprovalLevelsAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        return array_values(array_filter(array_map(function ($v) {
            $v = trim($v);
            return $v !== '' ? (ctype_digit($v) ? (int) $v : $v) : null;
        }, explode(',', $value)), function ($v) {
            return !is_null($v);
        }));
    }

    public function setApprovalLevelsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['approval_levels'] = implode(',', array_map('strval', $value));
        } else {
            $this->attributes['approval_levels'] = $value;
        }
    }

    public function getRepaymentOrderAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        return array_values(array_filter(array_map(function ($v) {
            $v = trim($v);
            return $v !== '' ? $v : null;
        }, explode(',', $value)), function ($v) {
            return !is_null($v);
        }));
    }

    public function setRepaymentOrderAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['repayment_order'] = implode(',', $value);
        } else {
            $this->attributes['repayment_order'] = $value;
        }
    }
}
