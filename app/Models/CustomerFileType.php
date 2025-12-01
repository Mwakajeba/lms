<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerFileType extends Model
{
    use HasFactory,LogsActivity;

    protected $table = 'customer_file_types';

    protected $fillable = [
        'customer_id',
        'filetype_id',
        'document_path',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function filetype()
    {
        return $this->belongsTo(Filetype::class);
    }
}
