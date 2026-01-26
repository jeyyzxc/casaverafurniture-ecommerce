<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'category_id',
        'price',
        'sale_price',
        'cost_price',
        'sale_starts_at',
        'sale_ends_at',
        'stock_quantity',
        'low_stock_threshold',
        'stock_status',
        'track_inventory',
        'allow_backorder',
        'status',
        'is_featured',
        'is_new',
        'is_bestseller',
        'dimensions',
        'weight',
        'material',
        'color',
        'attributes',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'view_count',
        'order_count',
        'average_rating',
        'review_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'weight' => 'decimal:2',
            'average_rating' => 'decimal:2',
            'attributes' => 'array',
            'track_inventory' => 'boolean',
            'allow_backorder' => 'boolean',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'is_bestseller' => 'boolean',
            'sale_starts_at' => 'datetime',
            'sale_ends_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    // Accessors
    public function getCurrentPriceAttribute(): float
    {
        if ($this->sale_price && $this->isOnSale()) {
            return (float) $this->sale_price;
        }
        return (float) $this->price;
    }

    public function isOnSale(): bool
    {
        if (!$this->sale_price) {
            return false;
        }

        $now = now();
        if ($this->sale_starts_at && $now < $this->sale_starts_at) {
            return false;
        }
        if ($this->sale_ends_at && $now > $this->sale_ends_at) {
            return false;
        }

        return true;
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('display_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_product')
            ->withPivot('display_order')
            ->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag')->withTimestamps();
    }

    public function relatedProducts()
    {
        return $this->belongsToMany(Product::class, 'related_products', 'product_id', 'related_product_id')
            ->withPivot('relation_type', 'display_order')
            ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'in_stock');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    // Stock management
    public function decrementStock(int $quantity): void
    {
        if ($this->track_inventory) {
            $this->decrement('stock_quantity', $quantity);
            $this->updateStockStatus();
        }
    }

    public function incrementStock(int $quantity): void
    {
        if ($this->track_inventory) {
            $this->increment('stock_quantity', $quantity);
            $this->updateStockStatus();
        }
    }

    protected function updateStockStatus(): void
    {
        if ($this->stock_quantity <= 0) {
            $this->update(['stock_status' => 'out_of_stock']);
        } elseif ($this->stock_quantity <= $this->low_stock_threshold) {
            // Low stock - could trigger alert
        } else {
            $this->update(['stock_status' => 'in_stock']);
        }
    }

    // Stock status helpers
    public function isLowStock(): bool
    {
        if (!$this->track_inventory) {
            return false;
        }

        return $this->stock_quantity > 0 
            && $this->low_stock_threshold > 0 
            && $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        if (!$this->track_inventory) {
            return false;
        }

        return $this->stock_quantity <= 0 || $this->stock_status === 'out_of_stock';
    }

    public function isInStock(): bool
    {
        if (!$this->track_inventory) {
            return true; // If not tracking, assume in stock
        }

        return $this->stock_quantity > 0 && $this->stock_status === 'in_stock';
    }
}
