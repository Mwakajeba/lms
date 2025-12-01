<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Microfinance extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to get only records with valid email addresses
     */
    public function scopeWithValidEmail($query)
    {
        return $query->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->whereRaw('email REGEXP "^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$"');
    }

    /**
     * Alias to match existing controller usage: withValidEmails()
     */
    public function scopeWithValidEmails($query)
    {
        return $this->scopeWithValidEmail($query);
    }

    /**
     * Get formatted name for email
     */
    public function getFormattedNameAttribute()
    {
        return $this->name ?: 'Valued Customer';
    }

    /**
     * Accessor used by existing bulk email flow: display_name
     */
    public function getDisplayNameAttribute()
    {
        return $this->formatted_name;
    }
}