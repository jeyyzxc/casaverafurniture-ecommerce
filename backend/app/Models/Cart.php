<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'item_count',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'promotion_id',
        'coupon_code',
        'status',
        'converted_at',
        'abandoned_at',
        'last_activity_at',
        'currency',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'converted_at' => 'datetime',
            'abandoned_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function recalculate(): void
    {
        $subtotal = $this->items->sum('subtotal');
        $itemCount = $this->items->sum('quantity');

        $this->update([
            'subtotal' => $subtotal,
            'item_count' => $itemCount,
            'total' => $subtotal - $this->discount_amount + $this->tax_amount,
            'last_activity_at' => now(),
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAbandoned($query)
    {
        return $query->where('status', 'abandoned');
    }
}
