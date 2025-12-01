<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'date',
        'reference',
        'reference_type',
        'customer_id',
        'description',
        'attachment',
        'branch_id',
        'user_id',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(JournalItem::class);
    }
    public function getTotalAttribute()
    {
        return $this->items->sum('amount');
    }

    public function getDebitTotalAttribute()
    {
        return $this->items->where('nature', 'debit')->sum('amount');
    }

    public function getCreditTotalAttribute()
    {
        return $this->items->where('nature', 'credit')->sum('amount');
    }
    public function getBalanceAttribute()
    {
        return $this->debit_total - $this->credit_total;
    }
    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('Y-m-d') : null;
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 2);
    }
    public function getFormattedDebitTotalAttribute()
    {
        return number_format($this->debit_total, 2);
    }
    public function getFormattedCreditTotalAttribute()
    {
        return number_format($this->credit_total, 2);
    }
    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 2);
    }
    
}
