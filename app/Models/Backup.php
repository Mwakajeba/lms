<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Vinkla\Hashids\Facades\Hashids;

class Backup extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'name',
        'filename',
        'file_path',
        'type',
        'size',
        'description',
        'status',
        'created_by',
        'company_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForCompany($query)
    {
        return $query->where('company_id', current_company_id());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDownloadUrlAttribute()
    {
        return route('settings.backup.download', $this->id);
    }

    public function getFilePathAttribute($value)
    {
        return $value;
    }

    public function setFilePathAttribute($value)
    {
        $this->attributes['file_path'] = $value;
    }

    public function deleteFile()
    {
        $fullPath = storage_path('app/' . $this->file_path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    /**
     * Get the hash ID for the model.
     */
    public function getHashIdAttribute()
    {
        return Hashids::encode($this->id);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'hash_id';
    }

    /**
     * Resolve the model binding using hash ID.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $id = Hashids::decode($value);
        return static::where('id', $id)->first();
    }

    /**
     * Get the route key value.
     */
    public function getRouteKey()
    {
        return $this->hash_id;
    }
}
