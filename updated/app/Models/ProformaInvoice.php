<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProformaInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'tenant_id',
        'duka_id',
        'customer_id',
        'invoice_date',
        'valid_until',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'notes',
        'status',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function duka(): BelongsTo
    {
        return $this->belongsTo(Duka::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProformaInvoiceItem::class);
    }

    public static function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        $lastInvoice = self::whereYear('created_at', $year)
                          ->whereMonth('created_at', $month)
                          ->orderBy('id', 'desc')
                          ->first();

        $sequence = $lastInvoice ? intval(substr($lastInvoice->invoice_number, -4)) + 1 : 1;
        return 'PF-' . $year . $month . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
