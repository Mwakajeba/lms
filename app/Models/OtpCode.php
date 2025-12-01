<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class OtpCode extends Model
{
    protected $fillable = ['phone', 'code', 'expires_at', 'is_used'];

    protected $dates = ['expires_at'];
}
