<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanFile extends Model
{
    use HasFactory,LogsActivity;
    protected $table = 'loan_files';
    protected $fillable = ['loan_id', 'file_type_id', 'file_path'];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function fileType()
    {
        return $this->belongsTo(Filetype::class);
    }
}
