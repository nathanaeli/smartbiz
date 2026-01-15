<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;   // ← REQUIRED
use Spatie\Activitylog\LogOptions;

class Customer extends Model
{
    use HasFactory, LogsActivity;              // ← REQUIRED

    protected $fillable = [
        'tenant_id',
        'duka_id',
        'name',
        'email',
        'phone',
        'address',
        'status',
        'created_by',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function duka(): BelongsTo
    {
        return $this->belongsTo(Duka::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('customer')          // ← Optional but recommended
            ->logFillable()                   // Log fillable fields only
            ->logOnlyDirty()                  // Log only changed fields
            ->dontSubmitEmptyLogs();          // Avoid empty logs
    }
}
