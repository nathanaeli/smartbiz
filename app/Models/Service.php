<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'duka_id',
        'category_id',
        'name',
        'description',
        'price',
        'billing_type',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function duka()
    {
        return $this->belongsTo(Duka::class);
    }
}
