<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStockItem extends Model
{
    use HasFactory;

    protected $table = 'product_stock_items';

    // ---------------------------------------------------------------
    // ATTRIBUTES (fillable)
    // ---------------------------------------------------------------
    protected $fillable = [
        'stock_id',      // Link to Stock table
        'product_id',    // Link to Product table
        'qr_code',       // Unique QR code for each item
        'status',        // available | sold
        'expiry_date',   // optional
    ];

    // ---------------------------------------------------------------
    // CASTS
    // ---------------------------------------------------------------
    protected $casts = [
        'expiry_date' => 'date',
    ];

    // ---------------------------------------------------------------
    // RELATIONSHIPS
    // ---------------------------------------------------------------
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ---------------------------------------------------------------
    // ACCESSORS
    // ---------------------------------------------------------------

    // Check if the item is sold
    public function getIsSoldAttribute(): bool
    {
        return $this->status === 'sold';
    }

    // Check if the item is available
    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available';
    }

    // Return a formatted expiry (e.g. "12 Jan 2026")
    public function getFormattedExpiryAttribute(): ?string
    {
        return $this->expiry_date?->format('d M Y');
    }

    // Friendly status badge
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'available' => 'badge bg-success',
            'sold'      => 'badge bg-danger',
            default     => 'badge bg-secondary',
        };
    }

    // ---------------------------------------------------------------
    // SCOPES
    // ---------------------------------------------------------------

    // Only available items
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    // Only sold items
    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    // Find by QR code
    public function scopeByQr($query, string $qr)
    {
        return $query->where('qr_code', $qr);
    }
}
