<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Activity
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_log';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'properties' => 'collection',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'tenant_id' => 'integer',
        'duka_id' => 'integer',
    ];

    /**
     * Relations: Who performed the action?
     */
    public function causer(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relations: What was the action performed on?
     */
    public function subject(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relations: Which Tenant does this belong to?
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relations: Which Duka does this belong to?
     */
    public function duka()
    {
        return $this->belongsTo(Duka::class);
    }

    /**
     * Scope: Filter by Tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope: Filter by Duka
     */
    public function scopeForDuka($query, $dukaId)
    {
        return $query->where('duka_id', $dukaId);
    }

    /**
     * Accessor: Human readable description
     * Example: "User X updated Product Y"
     */
    public function getDescriptionHumanAttribute()
    {
        $causer = $this->causer ? $this->causer->name : 'System';
        $subject = $this->subject_type ? class_basename($this->subject_type) : 'Generic';

        return "{$causer} " . strtolower($this->description) . " {$subject}";
    }

    // Helper to get diffs easily
    public function getChangesAttribute(): \Illuminate\Support\Collection
    {
        $properties = $this->properties;

        // If properties have 'old' and 'attributes' (Spatie default for updates)
        if (isset($properties['old']) && isset($properties['attributes'])) {
            return collect([
                'old' => $properties['old'],
                'new' => $properties['attributes'],
            ]);
        }

        return $properties;
    }
}
