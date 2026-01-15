<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;   // ← REQUIRED
use Spatie\Activitylog\LogOptions;

class StockTransfer extends Model
{
    use HasFactory, LogsActivity;              // ← REQUIRED

    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function stockTransferItem(): BelongsTo
    {
        return $this->belongsTo(StockTransferItem::class, 'stock_transfer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('stock_transfer')   // recommended
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) =>
                "Transferred {$this->quantity} pcs of {$this->product?->name} from {$this->stockTransferItem?->fromDuka?->name} to {$this->stockTransferItem?->toDuka?->name}"
            );
    }
}
