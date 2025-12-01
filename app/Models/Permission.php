<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'permission_group_id'
    ];

    /**
     * Get the permission group that owns the permission.
     */
    public function permissionGroup()
    {
        return $this->belongsTo(PermissionGroup::class);
    }

    /**
     * Get the group name for backward compatibility
     */
    public function getGroupAttribute()
    {
        return $this->permissionGroup ? $this->permissionGroup->name : null;
    }

    /**
     * Set the group by finding the permission group
     */
    public function setGroupAttribute($value)
    {
        if ($value) {
            $permissionGroup = PermissionGroup::where('name', $value)->first();
            $this->permission_group_id = $permissionGroup ? $permissionGroup->id : null;
        } else {
            $this->permission_group_id = null;
        }
    }
} 