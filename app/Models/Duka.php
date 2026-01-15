<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\Customer;

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
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // ✔ FIXED — Duka belongs to Tenant, NOT User
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
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

    public function stockTransferItemsFrom(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'from_duka_id');
    }

    public function stockTransferItemsTo(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'to_duka_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productCategories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'category_duka');
    }

    public function customers()
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



    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('duka')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
