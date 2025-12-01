<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashCollateralType extends Model
{
    use HasFactory,LogsActivity;
    protected $table = 'cash_collateral_types';

    protected $fillable = [
        'name',
        'chart_account_id',
        'description',
        'is_active',
    ];

    public function chartAccount()
    {
        return $this->belongsTo(ChartAccount::class, 'chart_account_id');
    }
}
