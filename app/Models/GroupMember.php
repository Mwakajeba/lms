<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'group_id',
        'customer_id',
        'status',
        'joined_date',
        'left_date',
        'notes',
    ];

    protected $casts = [
        'joined_date' => 'date',
        'left_date' => 'date',
    ];

    /**
     * Get the group that this member belongs to.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the customer who is a member.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
