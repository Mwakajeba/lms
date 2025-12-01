<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanTopup extends Model
{
    protected $fillable = [
        'old_loan_id', 'new_loan_id', 'old_balance', 'topup_amount', 'topup_type'
    ];

    public function oldLoan()
    {
        return $this->belongsTo(Loan::class, 'old_loan_id');
    }

    public function newLoan()
    {
        return $this->belongsTo(Loan::class, 'new_loan_id');
    }
}
