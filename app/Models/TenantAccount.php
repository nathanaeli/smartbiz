<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAccount extends Model
{
    protected $fillable = [
        'tenant_id',
        'company_name',
        'logo',
        'phone',
        'email',
        'address',
        'currency',
        'timezone',
        'website',
        'description',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    // Logo full URL accessor
    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/account/'.$this->logo) : asset('images/no-logo.png');
    }
}
