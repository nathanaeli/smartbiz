<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'products';

    protected $fillable = [
        'tenant_id',
        'duka_id',
        'category_id',
        'sku',
        'name',
        'description',
        'unit',
        'base_price',        // ← **BUYING PRICE** (cost to you)
        'selling_price',     // ← **SELLING PRICE** (price to customer)
        'is_active',
        'image',
        'barcode',
    ];

    protected $casts = [
        'base_price'     => 'decimal:2',
        'selling_price'  => 'decimal:2',
        'duka_id'        => 'integer',
        'category_id'    => 'integer',
        'is_active'      => 'boolean',
        'deleted_at'     => 'datetime',
    ];

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------
    public function duka(): BelongsTo
    {
        return $this->belongsTo(Duka::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductItem::class);
    }

    // -----------------------------------------------------------------
    // Accessors
    // -----------------------------------------------------------------
    public function getCurrentStockAttribute(): int
    {
        return $this->stocks()->sum('quantity');
    }

    public function getStockCostValueAttribute(): float
    {
        return $this->current_stock * $this->base_price;
    }

    public function getStockSellingValueAttribute(): float
    {
        return $this->current_stock * $this->selling_price;
    }

    public function getProfitPerUnitAttribute(): float
    {
        return $this->selling_price - $this->base_price;
    }

    public function getTotalProfitAttribute(): float
    {
        return $this->current_stock * $this->profit_per_unit;
    }

    public function getProfitMarginAttribute(): float
    {
        return $this->base_price > 0
            ? round((($this->selling_price - $this->base_price) / $this->base_price) * 100, 2)
            : 0;
    }

    public function getFormattedBasePriceAttribute(): string
    {
        return number_format($this->base_price, 2) . ' TSH';
    }

    public function getFormattedSellingPriceAttribute(): string
    {
        return number_format($this->selling_price, 2) . ' TSH';
    }

    public function getFormattedProfitAttribute(): string
    {
        return number_format($this->total_profit, 2) . ' TSH';
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('images/no-product.png');
        }

        // Check if image is already a full URL
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        // Otherwise, treat as local filename
        return asset('storage/products/' . $this->image);
    }

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->whereHas('stocks', fn($q) => $q->where('quantity', '>', 0));
    }

    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->whereHas('stocks', fn($q) => $q->where('quantity', '<=', $threshold));
    }

    public function scopeProfitable($query)
    {
        return $query->whereRaw('selling_price > base_price');
    }

    // -----------------------------------------------------------------
    // Activity Log
    // -----------------------------------------------------------------
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('product')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logOnly([
                'sku',
                'name',
                'category_id',
                'base_price',
                'selling_price',
                'unit',
                'description',
                'is_active',
            ])
            ->setDescriptionForEvent(fn(string $eventName) => "Product {$eventName}: {$this->name}");
    }

}
