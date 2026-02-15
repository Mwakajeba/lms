<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Vinkla\Hashids\Facades\Hashids;

class Branch extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'company_id', 'name', 'email', 'phone', 'address',
        'branch_id', 'branch_name', 'location', 'manager_name', 'status'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class, 'branch_id');
    }

    /**
     * Get the bank accounts assigned to this branch.
     */
    public function bankAccounts(): BelongsToMany
    {
        return $this->belongsToMany(BankAccount::class, 'bank_branch', 'branch_id', 'bank_account_id')
            ->withTimestamps();
    }

    /**
     * Get the hash ID for the branch
     *
     * @return string
     */
    public function getHashIdAttribute()
    {
        return Hashids::encode($this->id);
    }

    /**
     * Get the route key for the model
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Resolve the model from the route parameter
     *
     * @param string $value
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Try to decode the hash ID first
        $decoded = Hashids::decode($value);
        
        if (!empty($decoded)) {
            return static::where('id', $decoded[0])->first();
        }
        
        // Fallback to regular ID lookup
        return static::where('id', $value)->first();
    }

    /**
     * Get the route key for the model
     *
     * @return string
     */
    public function getRouteKey()
    {
        return $this->hash_id;
    }
}
