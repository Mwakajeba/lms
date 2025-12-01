<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountClassGroup extends Model
{
    use HasFactory , LogsActivity;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'account_class_groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'class_id',
        'group_code',
        'name',
        'company_id',
        'range_from',
        'range_to',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'range_from' => 'integer',
        'range_to' => 'integer',
    ];

    /**
     * Get the account class that owns the group.
     */
    public function accountClass(): BelongsTo
    {
        return $this->belongsTo(AccountClass::class, 'class_id');
    }

    /**
     * Get the chart accounts for this group.
     */
    public function chartAccounts(): HasMany
    {
        return $this->hasMany(ChartAccount::class, 'account_class_group_id');
    }

    /**
     * Get the company that owns the account class group.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
