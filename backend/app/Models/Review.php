<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'order_item_id',
        'reviewer_name',
        'reviewer_email',
        'rating',
        'title',
        'content',
        'pros',
        'cons',
        'status',
        'is_verified_purchase',
        'moderated_at',
        'moderated_by_admin_id',
        'moderation_notes',
        'is_featured',
        'helpful_count',
        'not_helpful_count',
    ];

    protected function casts(): array
    {
        return [
            'is_verified_purchase' => 'boolean',
            'is_featured' => 'boolean',
            'moderated_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function images()
    {
        return $this->hasMany(ReviewImage::class);
    }

    public function votes()
    {
        return $this->hasMany(ReviewVote::class);
    }

    public function replies()
    {
        return $this->hasMany(ReviewReply::class);
    }

    public function moderatedBy()
    {
        return $this->belongsTo(Admin::class, 'moderated_by_admin_id');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
