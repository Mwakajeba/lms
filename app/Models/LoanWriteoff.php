<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanWriteoff extends Model
{
    protected $table = 'loan_writeoffs';

    protected $fillable = [
        'loan_id',
        'customer_id',
        'outstanding',
        'reason',
        'writeoff_type',
        'createdby',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createdby');
    }
}
