<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashFlowCategory extends Model
{
    use  HasFactory,LogsActivity;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cash_flow_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the chart accounts for this cash flow category.
     */
    public function chartAccounts(): HasMany
    {
        return $this->hasMany(ChartAccount::class, 'cash_flow_category_id');
    }
}
