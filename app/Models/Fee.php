<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fee extends Model
{
    use HasFactory, SoftDeletes,LogsActivity;

    protected $fillable = [
        'name',
        'chart_account_id',
        'fee_type',
        'amount',
        'description',
        'status',
        'deduction_criteria',
        'include_in_schedule', // Added field
        'company_id',
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
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function chartAccount()
    {
        return $this->belongsTo(ChartAccount::class);
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

    public function scopeByFeeType($query, $feeType)
    {
        return $query->where('fee_type', $feeType);
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

    public function getFeeTypeBadgeAttribute()
    {
        return match ($this->fee_type) {
            'fixed' => '<span class="badge bg-primary">Fixed</span>',
            'percentage' => '<span class="badge bg-info">Percentage</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getDeductionCriteriaBadgeAttribute()
    {
        $options = self::getDeductionCriteriaOptions();
        $label = $options[$this->deduction_criteria] ?? 'Unknown';

        return match ($this->deduction_criteria) {
            'do_not_include_in_loan_schedule' => '<span class="badge bg-secondary" title="' . $label . '">Not in Schedule</span>',
            'distribute_fee_evenly_to_all_repayments' => '<span class="badge bg-info" title="' . $label . '">Distribute Evenly</span>',
            'charge_fee_on_release_date' => '<span class="badge bg-success" title="' . $label . '">On Release</span>',
            'charge_fee_on_first_repayment' => '<span class="badge bg-warning" title="' . $label . '">First Repayment</span>',
            'charge_fee_on_last_repayment' => '<span class="badge bg-danger" title="' . $label . '">Last Repayment</span>',
            'charge_same_fee_to_all_repayments' => '<span class="badge bg-primary" title="' . $label . '">All Repayments</span>',
            default => '<span class="badge bg-secondary" title="' . $label . '">Unknown</span>',
        };
    }

    public function getFormattedAmountAttribute()
    {
        if ($this->fee_type === 'percentage') {
            return number_format($this->amount, 2) . '%';
        }
        return number_format($this->amount, 2);
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
        return $this->fee_type === 'fixed';
    }

    public function isPercentage()
    {
        return $this->fee_type === 'percentage';
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

    public static function getFeeTypeOptions()
    {
        return [
            'fixed' => 'Fixed Amount',
            'percentage' => 'Percentage',
        ];
    }

    public static function getDeductionCriteriaOptions()
    {
        return [
            'do_not_include_in_loan_schedule' => 'Do not include in loan schedule',
            'distribute_fee_evenly_to_all_repayments' => 'Distribute fee evenly to all repayments',
            'charge_fee_on_release_date' => 'Charge fee on release date',
            'charge_fee_on_first_repayment' => 'Charge fee on the first repayment',
            'charge_fee_on_last_repayment' => 'Charge fee on the last repayment',
            'charge_same_fee_to_all_repayments' => 'Charge same fee to all repayments',
        ];
    }
}
