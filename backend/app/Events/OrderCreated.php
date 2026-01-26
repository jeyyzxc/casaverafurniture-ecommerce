<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
        //
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
        return 'order.created';
    }

    public function broadcastWith(): array
    {
        try {
            return [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'user_id' => $this->order->user_id,
                'customer_name' => $this->order->customer_name,
                'customer_email' => $this->order->customer_email,
                'status' => $this->order->status,
                'payment_status' => $this->order->payment_status,
                'total' => $this->order->total,
                'item_count' => $this->order->items ? $this->order->items->count() : 0,
                'created_at' => $this->order->created_at ? $this->order->created_at->toIso8601String() : now()->toIso8601String(),
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            \Log::warning('Error preparing broadcast data for OrderCreated event', [
                'order_id' => $this->order->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return [
                'order_id' => $this->order->id ?? null,
                'order_number' => $this->order->order_number ?? null,
                'timestamp' => now()->toIso8601String(),
            ];
        }
    }
}
