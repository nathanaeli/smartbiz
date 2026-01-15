<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Stock extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    // -----------------------------------------------------------------
    // Table & Guarded
    // -----------------------------------------------------------------
    protected $table = 'stocks';

    protected $fillable = [
        'duka_id',
        'product_id',
        'quantity',
        'last_updated_by',
        'batch_number',     // ← optional
        'expiry_date',      // ← optional
        'notes',            // ← optional
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'duka_id'         => 'integer',
        'product_id'      => 'integer',
        'last_updated_by' => 'integer',
        'expiry_date'     => 'date',
        'deleted_at'      => 'datetime',
    ];

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------
    public function duka(): BelongsTo
    {
        return $this->belongsTo(Duka::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // -----------------------------------------------------------------
    // Accessors
    // -----------------------------------------------------------------
    public function getValueAttribute(): float
    {
        return $this->quantity * ($this->product?->base_price ?? 0);
    }

    public function getFormattedValueAttribute(): string
    {
        return number_format($this->value) . ' TZS';
    }

    public function getStatusAttribute(): string
    {
        if ($this->quantity <= 0) return 'Out of Stock';
        if ($this->quantity < 10) return 'Low Stock';
        if ($this->quantity < 50) return 'Medium';
        return 'Good';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'Out of Stock' => 'bg-danger',
            'Low Stock'    => 'bg-danger',
            'Medium'       => 'bg-warning',
            'Good'         => 'bg-success',
            default        => 'bg-secondary',
        };
    }

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------
    public function scopeLow($query, $limit = 10)
    {
        return $query->where('quantity', '<=', $limit);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', 0);
    }

    // -----------------------------------------------------------------
    // Activity Log
    // -----------------------------------------------------------------
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('stock')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logOnly(['quantity', 'last_updated_by', 'batch_number', 'expiry_date'])
            ->setDescriptionForEvent(fn(string $eventName) =>
                "Stock {$eventName}: {$this->product?->name} (Qty: {$this->quantity})"
            );
    }


    public function recordFlow(int $qty, float $price, string $type, string $reason, $itemId = null, $notes = null)
{
    return \DB::transaction(function () use ($qty, $price, $type, $reason, $itemId, $notes) {
        $previousQty = $this->quantity;
        $newQty = ($type === 'in') ? ($previousQty + $qty) : ($previousQty - $qty);

        // Determine if this is an expense (buying) or income (selling)
        // unit_cost is recorded on 'in', unit_price is recorded on 'out'
        $unitCost = ($type === 'in') ? $price : 0;
        $unitPrice = ($type === 'out' && $reason === 'sale') ? $price : 0;

        $movement = $this->movements()->create([
            'user_id'           => auth()->id(),
            'product_item_id'   => $itemId,
            'type'              => $type,
            'quantity_change'   => $qty,
            'previous_quantity' => $previousQty,
            'new_quantity'      => $newQty,
            'unit_cost'         => $unitCost,
            'unit_price'        => $unitPrice,
            'total_value'       => $qty * $price,
            'reason'            => $reason,
            'notes'             => $notes,
        ]);

        $this->update(['quantity' => $newQty, 'last_updated_by' => auth()->id()]);

        return $movement;
    });
}
}
