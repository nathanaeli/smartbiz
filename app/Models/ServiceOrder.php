<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'tenant_id',
        'duka_id',
        'customer_id',
        'service_id',
        'service_type',
        'amount_paid',
        'status',
        'scheduled_at',
        'completed_at',
        'notes',
        'sale_id',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Auto-generate a unique Order Number when creating a new record.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            // Generates a format like: SRV-ABC123DE
            $order->order_number = 'SRV-' . strtoupper(Str::random(8));
        });
    }

    /**
     * Relationships
     */

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function duka()
    {
        return $this->belongsTo(Duka::class);
    }

    /**
     * Scopes for easy filtering
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
