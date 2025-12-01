<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'model',
        'action',
        'description',
        'ip_address',
        'device',
        'activity_time',
    ];

    protected $casts = [
        'activity_time' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
