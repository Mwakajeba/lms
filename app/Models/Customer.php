<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'customerNo',
        'name',
        'description', // Added description
        'work',
        'workAddress',
        'phone1',
        'phone2',
        'registrar',
        'idType',
        'idNumber',
        'dob',
        'region_id',
        'district_id',
        'branch_id',
        'company_id',
        'sex',
        'password',
        'dateRegistered',
        'relation',
        'photo',
        'document',
        'has_cash_collateral',
        'category',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'dob' => 'date',
        'dateRegistered' => 'date',
        'has_cash_collateral' => 'boolean',
    ];

    // Relationships
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'registrar');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function collaterals()
    {
        return $this->hasMany(CashCollateral::class);
    }

    public function repayments()
    {
        return $this->hasMany(Repayment::class, 'customer_id');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function shedule()
    {
        return $this->hasMany(LoanSchedule::class, 'customer_id');
    }
    public function guaranteedLoans()
    {
        return $this->belongsToMany(Loan::class, 'loan_guarantor')
            ->withPivot('relation')
            ->withTimestamps();
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members', 'customer_id', 'group_id');
    }

    public function loanOfficers()
    {
        return $this->belongsToMany(User::class, 'customer_officer', 'customer_id', 'officer_id');
    }

    // Accessor for loan officer IDs
    public function getLoanOfficerIdsAttribute()
    {
        return $this->loanOfficers()->pluck('users.id')->toArray();
    }

    public function ledGroups()
    {
        return $this->hasMany(Group::class, 'group_leader');
    }


    // Mutator for customer number
    public function setCustomerNoAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['customerNo'] = 100000 + (self::max('id') ?? 0) + 1;
        } else {
            $this->attributes['customerNo'] = $value;
        }
    }

    public function filetypes()
    {
        return $this->belongsToMany(Filetype::class, 'customer_file_types')
            ->withPivot('id', 'document_path')
            ->withTimestamps();
    }

    public function getCashCollateralBalanceAttribute()
    {
        return 20000000;
    }



}
