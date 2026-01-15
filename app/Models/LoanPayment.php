<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'amount',
        'payment_date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];
    protected static function booted()
    {
        static::created(function ($payment) {
            // Automatically record this as Income in the Transactions table
            Transaction::create([
                'duka_id' => $payment->sale->duka_id,
                'user_id' => $payment->user_id,
                'type' => 'income',
                'category' => 'loan_repayment',
                'amount' => $payment->amount,
                'reference_id' => $payment->id, // Links back to this specific payment
                'description' => "Debt payment for Sale #" . $payment->sale_id . " - " . $payment->sale->customer->name,
                'transaction_date' => $payment->payment_date ?? now(),
                'status' => 'active',
            ]);
        });
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
