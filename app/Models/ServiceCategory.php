<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $table = 'service_categories';

    protected $fillable = [
        'tenant_id',
        'duka_id',
        'name',
        'description',
    ];

    /**
     * Relationships
     */

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
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
