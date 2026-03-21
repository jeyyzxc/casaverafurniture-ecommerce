<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'unit_price',
        'sale_price',
        'subtotal',
        'product_name',
        'product_sku',
        'product_image',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'options' => 'array',
        ];
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function calculateSubtotal(): float
    {
        $price = $this->sale_price ?? $this->unit_price;
        return $price * $this->quantity;
    }
}
