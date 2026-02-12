<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Duka extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'name',
        'location',
        'manager_name',
        'latitude',
        'longitude',
        'status',
        'business_type', // Added: 'product', 'service', or 'both'
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // -----------------------------------------------------------------
    // Business Logic Helpers
    // -----------------------------------------------------------------

    /**
     * Determine if this Duka handles physical products.
     */
    public function supportsProducts(): bool
    {
        return in_array($this->business_type, ['product', 'both']);
    }

    /**
     * Determine if this Duka handles intangible services.
     */
    public function supportsServices(): bool
    {
        return in_array($this->business_type, ['service', 'both']);
    }

    // -----------------------------------------------------------------
    // Relationships - Core & Subscriptions
    // -----------------------------------------------------------------

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function dukaSubscriptions(): HasMany
    {
        return $this->hasMany(DukaSubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(DukaSubscription::class)
            ->where('status', 'active')
            ->whereDate('end_date', '>=', now())
            ->latest();
    }

    public function currentPlan()
    {
        return optional($this->activeSubscription)->plan;
    }

    // -----------------------------------------------------------------
    // Relationships - Products & Inventory
    // -----------------------------------------------------------------

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productCategories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'category_duka');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    // -----------------------------------------------------------------
    // Relationships - Services (The New Mechanism)
    // -----------------------------------------------------------------

    /**
     * Categories specifically for services (e.g., Consultations, Repairs)
     */
    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class);
    }

    /**
     * Intangible services offered by this Duka
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    // -----------------------------------------------------------------
    // Relationships - Operations & Customers
    // -----------------------------------------------------------------

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function stockTransfers(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'from_duka_id');
    }

    public function stockTransferItemsFrom(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'from_duka_id');
    }

    public function stockTransferItemsTo(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'to_duka_id');
    }

    // -----------------------------------------------------------------
    // Activity Log Configuration
    // -----------------------------------------------------------------

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('duka')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
