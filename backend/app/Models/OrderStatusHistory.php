<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    protected $table = 'order_status_history';

    protected $fillable = [
        'order_id',
        'status',
        'previous_status',
        'notes',
        'changed_by_type',
        'admin_id',
        'location',
        'latitude',
        'longitude',
        'notification_sent',
        'notification_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'notification_sent' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'notification_sent_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
