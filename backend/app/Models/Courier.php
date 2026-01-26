<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Courier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'logo',
        'tracking_url',
        'has_api_integration',
        'api_config',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'api_config' => 'array',
            'has_api_integration' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function shippingZones()
    {
        return $this->belongsToMany(ShippingZone::class, 'courier_shipping_zone')
            ->withPivot('rate_adjustment', 'is_active')
            ->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
