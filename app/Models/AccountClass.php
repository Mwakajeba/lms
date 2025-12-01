<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountClass extends Model
{
    use HasFactory,LogsActivity;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'account_class';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
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
     * Get the account class groups for this class.
     */
    public function accountClassGroups(): HasMany
    {
        return $this->hasMany(AccountClassGroup::class, 'class_id');
    }
}
