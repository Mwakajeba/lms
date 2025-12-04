<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectLoanThreshold extends Model
{
    protected $fillable = [
        'company_id',
        'loan_product_id',
        'max_amount',
        'description',
    ];

    public function scopeForCompany($query)
    {
        return $query->where('company_id', current_company_id());
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function loanProduct()
    {
        return $this->belongsTo(LoanProduct::class);
    }
}
