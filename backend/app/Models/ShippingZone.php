<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingZone extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'regions',
        'postal_codes',
        'base_rate',
        'free_shipping_threshold',
        'min_delivery_days',
        'max_delivery_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'regions' => 'array',
            'postal_codes' => 'array',
            'base_rate' => 'decimal:2',
            'free_shipping_threshold' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function rates()
    {
        return $this->hasMany(ShippingRate::class, 'shipping_zone_id');
    }

    public function couriers()
    {
        return $this->belongsToMany(Courier::class, 'courier_shipping_zone')
            ->withPivot('rate_adjustment', 'is_active')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class, 'shipping_zone_id');
    }
}
