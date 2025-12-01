<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Penalty extends Model
{
    use HasFactory, SoftDeletes,LogsActivity;

    protected $fillable = [
        'name',
        'penalty_income_account_id',
        'penalty_receivables_account_id',
        'penalty_type',
        'charge_frequency',
        'amount',
        'deduction_type',
        'description',
        'status',
        'branch_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function penaltyIncomeAccount()
    {
        return $this->belongsTo(ChartAccount::class, 'penalty_income_account_id');
    }

    public function penaltyReceivablesAccount()
    {
        return $this->belongsTo(ChartAccount::class, 'penalty_receivables_account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByPenaltyType($query, $penaltyType)
    {
        return $query->where('penalty_type', $penaltyType);
    }

    public function scopeByDeductionType($query, $deductionType)
    {
        return $query->where('deduction_type', $deductionType);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'active' => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-secondary">Inactive</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getPenaltyTypeBadgeAttribute()
    {
        return match ($this->penalty_type) {
            'fixed' => '<span class="badge bg-primary">Fixed</span>',
            'percentage' => '<span class="badge bg-info">Percentage</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getDeductionTypeBadgeAttribute()
    {
        return match ($this->deduction_type) {
            'over_due_principal_amount' => '<span class="badge bg-danger">Over Due Principal</span>',
            'over_due_interest_amount' => '<span class="badge bg-warning">Over Due Interest</span>',
            'over_due_principal_and_interest' => '<span class="badge bg-danger">Over Due Principal & Interest</span>',
            'total_principal_amount_released' => '<span class="badge bg-info">Total Principal Released</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getFormattedAmountAttribute()
    {
        if ($this->penalty_type === 'percentage') {
            return number_format($this->amount, 2) . '%';
        }
        return number_format($this->amount, 2);
    }

    public function getDeductionTypeLabelAttribute()
    {
        return match ($this->deduction_type) {
            'over_due_principal_amount' => 'Over Due Principal Amount',
            'over_due_interest_amount' => 'Over Due Interest Amount',
            'over_due_principal_and_interest' => 'Over Due Principal and Interest',
            'total_principal_amount_released' => 'Total Principal Amount Released',
            default => 'Unknown',
        };
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }

    // Methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isFixed()
    {
        return $this->penalty_type === 'fixed';
    }

    public function isPercentage()
    {
        return $this->penalty_type === 'percentage';
    }

    public function isOutstandingAmountDeduction()
    {
        return $this->deduction_type === 'over_due_principal_amount';
    }

    public function isPrincipalDeduction()
    {
        return $this->deduction_type === 'total_principal_amount_released';
    }

    public function activate()
    {
        $this->update(['status' => 'active']);
    }

    public function deactivate()
    {
        $this->update(['status' => 'inactive']);
    }

    // Static methods
    public static function getStatusOptions()
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];
    }

    public static function getPenaltyTypeOptions()
    {
        return [
            'fixed' => 'Fixed Amount',
            'percentage' => 'Percentage',
        ];
    }

    public static function getChargeFrequencyOptions()
    {
        return [
            'daily' => 'Daily',
            'one_time' => 'One-time',
        ];
    }

    public static function getDeductionTypeOptions()
    {
        return [
            'over_due_principal_amount' => 'Over Due Principal Amount',
            'over_due_interest_amount' => 'Over Due Interest Amount',
            'over_due_principal_and_interest' => 'Over Due Principal and Interest',
            'total_principal_amount_released' => 'Total Principal Amount Released',
        ];
    }

}
