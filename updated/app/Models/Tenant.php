<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions; // ← REQUIRED
use Spatie\Activitylog\Traits\LogsActivity;

class Tenant extends Model
{
    use HasFactory, LogsActivity; // ← REQUIRED

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'user_id',
        'status',
        'default_password',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function dukas(): HasMany
    {
        return $this->hasMany(Duka::class);
    }

    public function dukaSubscriptions(): HasMany
    {
        return $this->hasMany(DukaSubscription::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function stockTransferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function canAddDuka(): bool
    {
        // Get the latest active subscription
        $subscription = $this->dukaSubscriptions()->where('status', 'active')->latest()->first();

        if (! $subscription || ! $subscription->plan) {
            return false;
        }

        return $this->dukas()->count() < $subscription->plan->max_dukas;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('tenant') // recommended for filtering
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getFinancialSummary($startDate = null, $endDate = null)
    {
        $query = StockMovement::whereHas('stock.duka', function ($q) {
            $q->where('tenant_id', $this->id);
        });

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return [
            'total_income'   => (float) $query->clone()->where('reason', 'sale')->sum('total_value'),
            'total_expenses' => (float) $query->clone()->where('reason', 'purchase')->sum('total_value'),
            'stock_loss'     => (float) $query->clone()->where('reason', 'damage')->sum('total_value'),
        ];
    }

    public function activeSubscription()
{
    return $this->hasOne(DukaSubscription::class)
        ->where('status', 'active')
        ->where('end_date', '>=', now()->toDateString())
        ->latest('end_date'); // Prioritize the one that expires furthest in the future
}

    public function tenantAccount()
{
        return $this->hasOne(TenantAccount::class);
}

}
