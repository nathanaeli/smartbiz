<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'quantity_change'   => 'integer',
        'previous_quantity' => 'integer',
        'new_quantity'      => 'integer',
        'unit_cost'         => 'decimal:2',
        'unit_price'        => 'decimal:2',
        'total_value'       => 'decimal:2',
        'expiry_date'       => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($movement) {
            // If we are adding stock (IN), automatically fill quantity_remaining
            if ($movement->type === 'in' && $movement->quantity_change > 0) {
                $movement->quantity_remaining = $movement->quantity_change;
            }
        });
    }

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Link to the specific item if recorded one-by-one.
     */
    public function productItem(): BelongsTo
    {
        return $this->belongsTo(ProductItem::class);
    }

    // -----------------------------------------------------------------
    // Helpers for Income/Expense Logic
    // -----------------------------------------------------------------

    /**
     * Is this a movement that cost the business money?
     */
    public function isExpense(): bool
    {
        return $this->type === 'in' && $this->reason === 'purchase';
    }

    /**
     * Is this a movement that brought money in?
     */
    public function isIncome(): bool
    {
        return $this->type === 'out' && $this->reason === 'sale';
    }

    public function getFormattedQuantityChangeAttribute(): string
    {
        $sign = $this->quantity_change > 0 ? '+' : '';
        return $sign . $this->quantity_change;
    }
}
