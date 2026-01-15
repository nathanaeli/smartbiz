<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Plan extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_cycle',
        'max_dukas',
        'max_products',
        'is_active',
        // 'features', // We move away from the JSON array to the relationship
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Subscriptions associated with this plan
     */
    public function dukaSubscriptions(): HasMany
    {
        return $this->hasMany(DukaSubscription::class);
    }

    /**
     * The Features linked to this Plan
     */
    public function planFeatures(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'feature_plan')
                    ->withPivot('value')
                    ->withTimestamps();
    }

    /**
     * Alias for planFeatures
     */
    public function features(): BelongsToMany
    {
        return $this->planFeatures();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}
