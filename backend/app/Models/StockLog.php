<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLog extends Model
{
    protected $fillable = [
        'product_id',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        'type',
        'reference_type',
        'reference_id',
        'reference_number',
        'notes',
        'admin_id',
        'admin_name',
        'unit_cost',
        'total_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity_change' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    /**
     * Get the product that owns the stock log
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the admin who made the change
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
