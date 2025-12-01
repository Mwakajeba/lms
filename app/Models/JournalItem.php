<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalItem extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'journal_id',
        'chart_account_id',
        'amount',
        'description',
        'nature',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function chartAccount()
    {
        return $this->belongsTo(ChartAccount::class);
    }

}
