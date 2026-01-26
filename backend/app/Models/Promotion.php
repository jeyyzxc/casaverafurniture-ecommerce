<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'buy_quantity',
        'get_quantity',
        'get_discount_percentage',
        'applies_to',
        'applicable_product_ids',
        'applicable_category_ids',
        'applicable_collection_ids',
        'min_order_amount',
        'max_order_amount',
        'min_quantity',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'starts_at',
        'ends_at',
        'first_order_only',
        'allowed_user_ids',
        'allowed_user_emails',
        'is_active',
        'is_visible',
        'auto_apply',
        'priority',
        'combinable_with_other_promotions',
        'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'applicable_product_ids' => 'array',
            'applicable_category_ids' => 'array',
            'applicable_collection_ids' => 'array',
            'allowed_user_ids' => 'array',
            'allowed_user_emails' => 'array',
            'discount_value' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_order_amount' => 'decimal:2',
            'get_discount_percentage' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'is_visible' => 'boolean',
            'auto_apply' => 'boolean',
            'first_order_only' => 'boolean',
            'combinable_with_other_promotions' => 'boolean',
        ];
    }

    public function usages()
    {
        return $this->hasMany(PromotionUsage::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    // Check if promotion is valid
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->starts_at && $now < $this->starts_at) {
            return false;
        }
        if ($this->ends_at && $now > $this->ends_at) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    // Calculate discount
    public function calculateDiscount(float $orderAmount): float
    {
        if ($this->discount_type === 'percentage') {
            $discount = $orderAmount * ($this->discount_value / 100);
            if ($this->max_discount_amount) {
                $discount = min($discount, $this->max_discount_amount);
            }
            return $discount;
        }

        if ($this->discount_type === 'fixed') {
            return min($this->discount_value, $orderAmount);
        }

        return 0;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
    }
}
