<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashCollateral extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'branch_id',
        'company_id',
        'customer_id',
        'type_id',
        'amount',
    ];

    // Relationships

    // A CashCollateral belongs to a Branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // A CashCollateral belongs to a Company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // A CashCollateral belongs to a Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // A CashCollateral belongs to a CashCollateralType
    public function type()
    {
        return $this->belongsTo(CashCollateralType::class, 'type_id');
    }
    
    public static function getCashCollateralBalance(int $customerId): float
    {
        $record = self::where('customer_id', $customerId)->first();
        return round($record?->amount ?? 0, 2);
    }
}
