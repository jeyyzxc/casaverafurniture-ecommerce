<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAlert extends Model
{
    protected $fillable = [
        'product_id',
        'alert_type',
        'current_quantity',
        'threshold_quantity',
        'is_acknowledged',
        'acknowledged_at',
        'acknowledged_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'is_acknowledged' => 'boolean',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(Admin::class, 'acknowledged_by_admin_id');
    }

    public function acknowledge(int $adminId): void
    {
        $this->update([
            'is_acknowledged' => true,
            'acknowledged_at' => now(),
            'acknowledged_by_admin_id' => $adminId,
        ]);
    }

    public function scopeUnacknowledged($query)
    {
        return $query->where('is_acknowledged', false);
    }
}
