<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->storeActivityLog('create');
        });

        static::updated(function ($model) {
            $model->storeActivityLog('update');
        });

        static::deleted(function ($model) {
            $model->storeActivityLog('delete');
        });
    }

    protected function storeActivityLog($action)
    {
        $agent = new Agent();
        $deviceInfo = 'Unknown';
        if ($agent->isDesktop()) {
            $deviceInfo = 'Desktop';
        } elseif ($agent->isPhone()) {
            if ($agent->is('iPhone')) {
                $deviceInfo = 'iPhone';
            } elseif ($agent->is('AndroidOS')) {
                $deviceInfo = 'Android Phone';
            } else {
                $deviceInfo = 'Phone';
            }
        } elseif ($agent->isTablet()) {
            if ($agent->is('iPad')) {
                $deviceInfo = 'iPad';
            } else {
                $deviceInfo = 'Tablet';
            }
        }

        $deviceString = $deviceInfo . ' - ' . $agent->browser();

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'model'       => class_basename($this),
            'action'      => $action,
            'description' => "{$action}d " . class_basename($this) . " (ID: {$this->id})",
            'ip_address'  => request()->ip(),
            'device'      => $deviceString,
            'activity_time' => now(),
        ]);
    }
}
