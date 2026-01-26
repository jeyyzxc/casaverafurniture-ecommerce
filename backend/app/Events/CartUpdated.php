<?php

namespace App\Events;

use App\Models\Cart;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CartUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Cart $cart,
        public string $action = 'update' // 'add', 'update', 'remove', 'clear'
    ) {}

    public function broadcastOn(): array
    {
        $channels = [new Channel('cart')];

        // If cart has a user, also broadcast to private user channel
        if ($this->cart->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->cart->user_id);
        }

        // If cart has a session ID, broadcast to session channel
        if ($this->cart->session_id) {
            $channels[] = new Channel('cart.session.' . $this->cart->session_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'cart.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'cart_id' => $this->cart->id,
            'user_id' => $this->cart->user_id,
            'session_id' => $this->cart->session_id,
            'item_count' => $this->cart->item_count,
            'subtotal' => $this->cart->subtotal,
            'total' => $this->cart->total,
            'action' => $this->action,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
