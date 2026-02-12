<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantOfficer extends Model
{
    protected $table = 'tenant_officers';

    protected $fillable = [
        'tenant_id',
        'duka_id',
        'officer_id',
        'role',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Tenant relationship
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Duka relationship
     */
    public function duka(): BelongsTo
    {
        return $this->belongsTo(Duka::class);
    }

    /**
     * Officer/User relationship
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }
}
