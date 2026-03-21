<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {
        
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('orders'),
            new PrivateChannel('user.' . $this->order->user_id),
            new Channel('admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'user_id' => $this->order->user_id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'status' => $this->order->status,
            'payment_status' => $this->order->payment_status,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
