<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlTransaction extends Model
{
    use HasFactory,LogsActivity;

    protected $table = 'gl_transactions';

    protected $fillable = [
        'chart_account_id',
        'customer_id',
        'supplier_id',
        'amount',
        'nature',
        'transaction_id',
        'transaction_type',
        'date',
        'description',
        'branch_id',
        'user_id',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    // Optional: Define relationships
    public function chartAccount()
    {
        return $this->belongsTo(ChartAccount::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'transaction_id');
    }

    public function paymentVoucher()
    {
        return $this->belongsTo(Payment::class, 'transaction_id');
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'transaction_id');
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'transaction_id');
    }

    /**
     * Boot method to register model events
     */
    protected static function boot()
    {
        parent::boot();

        // When a GL transaction is created
        static::created(function ($glTransaction) {
            $glTransaction->updateBankReconciliations();
        });

        // When a GL transaction is updated
        static::updated(function ($glTransaction) {
            $glTransaction->updateBankReconciliations();
        });

        // When a GL transaction is deleted
        static::deleted(function ($glTransaction) {
            $glTransaction->updateBankReconciliations();
        });
    }

    /**
     * Update bank reconciliations that are still in progress
     */
    public function updateBankReconciliations()
    {
        $service = app(\App\Services\BankReconciliationService::class);
        $service->updateReconciliationsForTransaction($this);
    }
}
