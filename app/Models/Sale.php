<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\Auditable;

    protected $casts = [
        'due_date' => 'date',
        'created_at' => 'datetime', // Cast it to ensure Carbon helps with formatting
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
        'created_at',
        'created_by',
        'profit_loss',
        'import_batch',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
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

    protected static function booted()
    {
        static::deleting(function ($sale) {
            foreach ($sale->saleItems as $item) {
                $item->restoreStock();
            }
            Transaction::where('reference_id', $sale->id)
                ->where('category', 'sale')
                ->update(['status' => 'void']);
            $sale->loanPayments()->delete();
        });
    }
}
