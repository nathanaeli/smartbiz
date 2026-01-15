<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPermission extends Model
{
    use HasFactory;

    protected $table = 'staff_permissions';

    protected $fillable = [
        'tenant_id',
        'officer_id',
        'duka_id',
        'permission_name',
        'is_granted',
    ];

    protected $casts = [
        'is_granted' => 'boolean',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function duka(): BelongsTo
    {
        return $this->belongsTo(Duka::class, 'duka_id');
    }

    // Scopes
    public function scopeGranted($query)
    {
        return $query->where('is_granted', true);
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByOfficer($query, $officerId)
    {
        return $query->where('officer_id', $officerId);
    }

    public function scopeByDuka($query, $dukaId)
    {
        return $query->where('duka_id', $dukaId);
    }

    public function scopeByPermission($query, $permissionName)
    {
        return $query->where('permission_name', $permissionName);
    }
}
