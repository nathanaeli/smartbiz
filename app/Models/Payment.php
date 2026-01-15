<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'payment_method',
        'transaction_id',
        'provider_reference',
        'amount',
        'currency',
        'status',
        'request_payload',
        'response_payload',
        'error_message',
    ];

    /**
     * A payment belongs to a tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * A payment belongs to a subscription
     */
    public function subscription()
    {
        return $this->belongsTo(DukaSubscription::class);
    }
}
