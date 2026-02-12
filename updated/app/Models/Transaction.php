<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use SoftDeletes, HasFactory, \App\Traits\Auditable;

    protected $fillable = [
        'duka_id',
        'user_id',
        'type',
        'category',
        'amount',
        'status',
        'payment_method',
        'description',
        'reference_id',
        'transaction_date',
        'created_at', // Allow manual timestamp for backdated sales/expenses
    ];

    protected $casts = [
        'transaction_date' => 'date',      // Usually stored as YYYY-MM-DD for grouping
        'created_at'       => 'datetime',  // The exact precise time
        'amount'           => 'decimal:2',
    ];

    // --- Status helpers ---

    public function isVoid(): bool
    {
        return $this->status === 'void';
    }

    /**
     * Returns true if the transaction is money coming in.
     */
    public function isIncome(): bool
    {
        return $this->type === 'income';
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

    // --- Attributes / Accessors ---

    /**
     * Formats the amount with a (+) or (-) sign for the UI.
     */
    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type === 'income' ? '+' : '-';
        return $prefix . ' ' . number_format((float)$this->amount, 2) . ' TSH';
    }

    // --- Boot Method (Business Logic) ---

    protected static function booted()
    {
        /**
         * Automatically ensure transaction_date matches created_at
         * if only one is provided during creation.
         */
        static::creating(function ($transaction) {
            if ($transaction->created_at && !$transaction->transaction_date) {
                $transaction->transaction_date = $transaction->created_at->toDateString();
            }
        });
    }
}
