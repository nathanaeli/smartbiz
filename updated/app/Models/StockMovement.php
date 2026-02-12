<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $table = 'stock_movements';

    protected $fillable = [
        'stock_id',
        'user_id',
        'product_item_id',
        'type',
        'quantity_change',
        'previous_quantity',
        'quantity_remaining',
        'new_quantity',
        'unit_cost',
        'unit_price',
        'total_value',
        'batch_number',
        'expiry_date',
        'notes',
        'reason',
        'import_batch',
        'created_at', // Enables backdating sync with Sales
    ];

    protected $casts = [
        'quantity_change'    => 'integer',
        'previous_quantity'  => 'integer',
        'quantity_remaining' => 'integer',
        'new_quantity'       => 'integer',
        'unit_cost'          => 'decimal:2',
        'unit_price'         => 'decimal:2',
        'total_value'        => 'decimal:2',
        'expiry_date'        => 'date',
        'created_at'         => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($movement) {
            // FIFO logic: If stock is coming in, it starts with full remaining quantity
            if ($movement->type === 'in' && $movement->quantity_change > 0) {
                if (is_null($movement->quantity_remaining)) {
                    $movement->quantity_remaining = $movement->quantity_change;
                }
            }
        });
    }

    // --- Relationships ---

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function productItem(): BelongsTo
    {
        return $this->belongsTo(ProductItem::class);
    }

    // --- Helpers ---

    public function isExpense(): bool
    {
        return $this->type === 'in' && $this->reason === 'purchase';
    }

    public function isIncome(): bool
    {
        return $this->type === 'out' && $this->reason === 'sale';
    }

    /**
     * Formats the quantity for the UI (e.g., -5 for sales, +10 for purchases)
     */
    public function getFormattedQuantityChangeAttribute(): string
    {
        $val = abs($this->quantity_change);
        return ($this->type === 'out' ? '-' : '+') . $val;
    }
}
