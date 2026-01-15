<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailablePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_active',
        'feature_id',
        'model',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The feature this permission belongs to.
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function staffPermissions(): HasMany
    {
        return $this->hasMany(StaffPermission::class, 'permission_name', 'name');
    }
}
