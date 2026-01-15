<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'qr_code',
        'status',
        'sold_at',
        'created_by',
        'stock_amount',

    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'stock_amount' => 'integer',
    ];

    /**
     * Get the product that owns this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope for available items.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope for sold items.
     */
    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    /**
     * Scope for damaged items.
     */
    public function scopeDamaged($query)
    {
        return $query->where('status', 'damaged');
    }

    /**
     * Mark item as sold.
     */
    public function markAsSold(): bool
    {
        return $this->update([
            'status' => 'sold',
            'sold_at' => now(),
        ]);
    }

    /**
     * Mark item as damaged.
     */
    public function markAsDamaged(): bool
    {
        return $this->update(['status' => 'damaged']);
    }

    /**
     * Mark item as available.
     */
    public function markAsAvailable(): bool
    {
        return $this->update([
            'status' => 'available',
            'sold_at' => null,
        ]);
    }

    /**
     * Generate unique QR code for this item.
     */
    public static function generateQrCode(): string
    {
        do {
            $qrCode = 'ITEM-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (self::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }

    /**
     * Find item by QR code.
     */
    public static function findByQrCode(string $qrCode): ?self
    {
        return self::where('qr_code', $qrCode)->first();
    }

    public function recordPurchase(float $costPrice)
{
    return \DB::transaction(function () use ($costPrice) {
        // 1. Mark status
        $this->update(['status' => 'available']);

        // 2. Find or create the main Stock record for this product
        $stock = Stock::firstOrCreate(
            ['product_id' => $this->product_id, 'duka_id' => auth()->user()->duka_id]
        );

        // 3. Record the movement (Expense)
        return $stock->recordFlow(
            qty: 1,
            price: $costPrice,
            type: 'in',
            reason: 'purchase',
            notes: "Individual item created: {$this->qr_code}"
        );
    });
}
}
