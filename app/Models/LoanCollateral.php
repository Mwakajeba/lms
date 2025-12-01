<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanCollateral extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'type',
        'title',
        'description',
        'estimated_value',
        'appraised_value',
        'status',
        'condition',
        'location',
        'valuation_date',
        'valuator_name',
        'notes',
        'serial_number',
        'registration_number',
        'images',
        'documents',
        'created_by',
        'updated_by',
        'status_changed_at',
        'status_changed_by',
        'status_change_reason',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'appraised_value' => 'decimal:2',
        'valuation_date' => 'date',
        'status_changed_at' => 'datetime',
        'images' => 'array',
        'documents' => 'array',
    ];

    // Collateral statuses
    const STATUS_ACTIVE = 'active';
    const STATUS_SOLD = 'sold';
    const STATUS_RELEASED = 'released';
    const STATUS_FORECLOSED = 'foreclosed';
    const STATUS_DAMAGED = 'damaged';
    const STATUS_LOST = 'lost';

    // Collateral conditions
    const CONDITION_EXCELLENT = 'excellent';
    const CONDITION_GOOD = 'good';
    const CONDITION_FAIR = 'fair';
    const CONDITION_POOR = 'poor';

    // Collateral types
    const TYPE_PROPERTY = 'property';
    const TYPE_VEHICLE = 'vehicle';
    const TYPE_EQUIPMENT = 'equipment';
    const TYPE_CASH = 'cash';
    const TYPE_JEWELRY = 'jewelry';
    const TYPE_ELECTRONICS = 'electronics';
    const TYPE_OTHER = 'other';

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'bg-success',
            self::STATUS_SOLD => 'bg-primary',
            self::STATUS_RELEASED => 'bg-info',
            self::STATUS_FORECLOSED => 'bg-warning',
            self::STATUS_DAMAGED => 'bg-danger',
            self::STATUS_LOST => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    public function getConditionBadgeClass()
    {
        return match($this->condition) {
            self::CONDITION_EXCELLENT => 'bg-success',
            self::CONDITION_GOOD => 'bg-primary',
            self::CONDITION_FAIR => 'bg-warning',
            self::CONDITION_POOR => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_SOLD => 'Sold',
            self::STATUS_RELEASED => 'Released',
            self::STATUS_FORECLOSED => 'Foreclosed',
            self::STATUS_DAMAGED => 'Damaged',
            self::STATUS_LOST => 'Lost',
        ];
    }

    public static function getConditionOptions()
    {
        return [
            self::CONDITION_EXCELLENT => 'Excellent',
            self::CONDITION_GOOD => 'Good',
            self::CONDITION_FAIR => 'Fair',
            self::CONDITION_POOR => 'Poor',
        ];
    }

    public static function getTypeOptions()
    {
        return [
            self::TYPE_PROPERTY => 'Property',
            self::TYPE_VEHICLE => 'Vehicle',
            self::TYPE_EQUIPMENT => 'Equipment',
            self::TYPE_CASH => 'Cash',
            self::TYPE_JEWELRY => 'Jewelry',
            self::TYPE_ELECTRONICS => 'Electronics',
            self::TYPE_OTHER => 'Other',
        ];
    }

    public function getImageUrls()
    {
        if (!$this->images) {
            return [];
        }
        
        return collect($this->images)->map(function ($image) {
            return asset('storage/' . $image);
        })->toArray();
    }

    public function getDocumentUrls()
    {
        if (!$this->documents) {
            return [];
        }
        
        return collect($this->documents)->map(function ($document) {
            return [
                'name' => basename($document),
                'url' => asset('storage/' . $document),
                'path' => $document
            ];
        })->toArray();
    }
}
