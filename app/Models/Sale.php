<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $casts = [
        'due_date' => 'date',
    ];

    protected $fillable = [
        'tenant_id',
        'duka_id',
        'customer_id',
        'total_amount',
        'discount_amount',
        'profit_loss',
        'is_loan',
        'due_date',
        'discount_reason',
        'total_payments',
        'remaining_balance',
        'payment_status',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function duka()
    {
        return $this->belongsTo(Duka::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function loanPayments()
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function getTotalPaymentsAttribute()
    {
        return $this->loanPayments->sum('amount');
    }

    public function getRemainingBalanceAttribute()
    {
        if ($this->is_loan) {
            return $this->total_amount - $this->total_payments;
        }
        return 0;
    }

    public function getPaymentStatusAttribute()
    {
        if (!$this->is_loan) {
            return 'N/A';
        }

        $remaining = $this->remaining_balance;
        if ($remaining <= 0) {
            return 'Fully Paid';
        } elseif ($this->total_payments > 0) {
            return 'Partially Paid';
        } else {
            return 'Unpaid';
        }
    }
}
