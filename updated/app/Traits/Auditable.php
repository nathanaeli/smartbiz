<?php

namespace App\Traits;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Contracts\Activity;

trait Auditable
{
    use LogsActivity;

    /**
     * Configure the activity logging options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes
            ->logOnlyDirty() // Only log changes
            ->dontSubmitEmptyLogs();
    }

    /**
     * Tap into the activity before it is saved.
     * Use this to add custom fields like tenant_id and duka_id.
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        // 1. IP Address & User Agent
        $activity->ip_address = request()->ip();
        $activity->user_agent = request()->userAgent();

        // 2. Tenant ID
        if (Auth::check() && Auth::user()->tenant_id) {
            $activity->tenant_id = Auth::user()->tenant_id;
        } elseif (isset($this->tenant_id)) {
            // Fallback: If model has tenant_id (e.g., seeding or background job)
            $activity->tenant_id = $this->tenant_id;
        } elseif (isset($this->duka) && $this->duka->tenant_id) {
            // Fallback: Infer from related Duka
            $activity->tenant_id = $this->duka->tenant_id;
        }

        // 3. Duka ID
        if (isset($this->duka_id)) {
            // Primary: If the model itself belongs to a Duka (Product, Sale, Stock)
            $activity->duka_id = $this->duka_id;
        } elseif (Auth::check()) {
            // Secondary: Try to infer from User (e.g. Officer assigned to a Duka)
            // This logic depends on whether User has a 'duka_id' or relation
            // $activity->duka_id = Auth::user()->current_duka_id ?? null; 
        }
    }
}
