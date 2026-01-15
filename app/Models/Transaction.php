<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
   use SoftDeletes;

    protected $fillable = [
        'duka_id', 'user_id', 'type', 'category',
        'amount', 'status', 'payment_method',
        'description', 'reference_id', 'transaction_date'
    ];
    protected $casts = [
        'transaction_date' => 'datetime', // or 'date'
        'amount' => 'decimal:2',
    ];

    // Status helpers
    public function isVoid(): bool
    {
        return $this->status === 'void';
    }
    // --- Relationships ---

    public function duka(): BelongsTo
    {
        return $this->belongsTo(Duka::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- Helpers ---

    /**
     * Returns true if the transaction is money coming in.
     */
    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    /**
     * Formats the amount with a (+) or (-) sign for the UI.
     */
    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type === 'income' ? '+' : '-';
        return $prefix . ' ' . number_format($this->amount, 2) . ' TSH';
    }
}
