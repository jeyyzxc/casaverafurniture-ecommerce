<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_email',
        'customer_name',
        'customer_phone',
        'status',
        'billing_name',
        'billing_address_line_1',
        'billing_address_line_2',
        'billing_city',
        'billing_province',
        'billing_postal_code',
        'billing_country',
        'billing_phone',
        'shipping_name',
        'shipping_address_line_1',
        'shipping_address_line_2',
        'shipping_city',
        'shipping_province',
        'shipping_postal_code',
        'shipping_country',
        'shipping_phone',
        'subtotal',
        'discount_amount',
        'shipping_amount',
        'tax_amount',
        'total',
        'currency',
        'promotion_id',
        'coupon_code',
        'shipping_zone_id',
        'shipping_rate_id',
        'courier_id',
        'shipping_method',
        'tracking_number',
        'tracking_url',
        'estimated_delivery_date',
        'shipped_at',
        'delivered_at',
        'payment_status',
        'paid_at',
        'notes',
        'admin_notes',
        'cancelled_at',
        'cancellation_reason',
        'source',
        'ip_address',
        'user_agent',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'estimated_delivery_date' => 'date',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function notes()
    {
        return $this->hasMany(OrderNote::class)->orderBy('created_at', 'desc');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        // Check if deleted_at column exists
        $hasDeletedAt = false;
        try {
            $hasDeletedAt = \Schema::hasColumn('payments', 'deleted_at');
        } catch (\Exception $e) {
            // If check fails, assume column doesn't exist
        }
        
        if ($hasDeletedAt) {
            // Column exists - use latestOfMany with SoftDeletes
            // Note: Don't select order_id in eager loading to avoid ambiguity with subquery
            return $this->hasOne(Payment::class)->latestOfMany();
        } else {
            // Column doesn't exist - use a subquery to get latest payment without SoftDeletes
            return $this->hasOne(Payment::class)
                ->whereRaw('payments.id = (SELECT MAX(p2.id) FROM payments p2 WHERE p2.order_id = orders.id)');
        }
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function shippingZone()
    {
        return $this->belongsTo(ShippingZone::class);
    }

    public function shippingRate()
    {
        return $this->belongsTo(ShippingRate::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    // Generate order number
    public static function generateOrderNumber(): string
    {
        $prefix = 'CV';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}{$date}{$random}";
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isShipped(): bool
    {
        return $this->status === 'shipped';
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    // Scopes
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
