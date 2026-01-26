<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'title',
        'subtitle',
        'content',
        'type',
        'settings',
        'product_ids',
        'category_ids',
        'collection_id',
        'display_order',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'product_ids' => 'array',
            'category_ids' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
