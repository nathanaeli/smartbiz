<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;   // ← REQUIRED
use Spatie\Activitylog\LogOptions;

class StockTransferItem extends Model
{
    use HasFactory, LogsActivity;              // ← REQUIRED

    protected $fillable = [
        'tenant_id',
        'from_duka_id',
        'to_duka_id',
        'transferred_by',
        'status',
        'reason',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function fromDuka(): BelongsTo
    {
        return $this->belongsTo(Duka::class, 'from_duka_id');
    }

    public function toDuka(): BelongsTo
    {
        return $this->belongsTo(Duka::class, 'to_duka_id');
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'stock_transfer_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('stock_transfer_item')   // recommended
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) =>
                "Stock transfer {$eventName}: from {$this->fromDuka?->name} to {$this->toDuka?->name} by {$this->transferredBy?->name}"
            );
    }
}
