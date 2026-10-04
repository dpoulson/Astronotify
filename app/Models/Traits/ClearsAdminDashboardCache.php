<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Trait ClearsAdminDashboardCache
 *
 * Automatically flushes the cached admin dashboard statistics whenever
 * a model instance using this trait is created or deleted.
 */
trait ClearsAdminDashboardCache
{
    /**
     * Boot the trait and register Eloquent event listeners.
     */
    protected static function bootClearsAdminDashboardCache(): void
    {
        static::created(function () {
            Cache::forget('admin_dashboard_stats_v2');
        });

        static::deleted(function () {
            Cache::forget('admin_dashboard_stats_v2');
        });
    }
}
