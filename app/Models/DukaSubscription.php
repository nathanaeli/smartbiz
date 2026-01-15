<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Spatie\Activitylog\Traits\LogsActivity; // Disabled due to Carbon issues
// use Spatie\Activitylog\LogOptions;

class DukaSubscription extends Model
{
    use HasFactory; // Activity logging disabled to avoid Carbon serialization issue
                    // use LogsActivity;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'amount',
        'start_date',
        'end_date',
        'plan_name',
        'status',
        'payment_method',
        'transaction_id',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'start_date' => 'date', // changed from datetime
        'end_date'   => 'date', // changed from datetime
        'status'     => 'string',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function duka(): BelongsTo
    {
        return $this->belongsTo(Duka::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->end_date >= now()->toDateString();
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'active' && $this->end_date < now()->toDateString();
    }

    /**
     * Get days remaining until expiration
     */
    public function getDaysRemaining(): int
    {
        if (! $this->isActive()) {
            return 0;
        }

        $now     = now()->startOfDay();
        $endDate = $this->end_date instanceof \Carbon\Carbon
            ? $this->end_date->startOfDay()
            : \Carbon\Carbon::parse($this->end_date)->startOfDay();

        return $now->diffInDays($endDate, false);
    }

    /**
     * Get subscription status with days remaining
     */
    public function getStatusWithDays(): array
    {
        if ($this->status !== 'active') {
            return ['status' => 'inactive', 'days_remaining' => 0];
        }

        if ($this->isExpired()) {
            return ['status' => 'expired', 'days_remaining' => 0];
        }

        return ['status' => 'active', 'days_remaining' => $this->getDaysRemaining()];
    }

    public function isTrial(): bool
    {
        return $this->status === 'trialing';
    }

/**
 * Scope to check if a tenant has any active/trialing subscription for a specific Duka
 */
    public function scopeActiveForDuka($query, $dukaId)
    {
        return $query->where('duka_id', $dukaId)
            ->whereIn('status', ['active', 'trialing'])
            ->where('end_date', '>=', now()->toDateString());
    }
}
