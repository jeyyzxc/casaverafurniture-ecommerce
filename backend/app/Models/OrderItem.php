<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'product_image',
        'product_description',
        'product_options',
        'quantity',
        'unit_price',
        'sale_price',
        'discount_amount',
        'tax_amount',
        'subtotal',
        'total',
        'status',
        'tracking_number',
    ];

    protected function casts(): array
    {
        return [
            'product_options' => 'array',
            'unit_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
