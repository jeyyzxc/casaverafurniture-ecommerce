<?php

namespace App\Events;

use App\Models\Promotion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PromotionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Promotion $promotion)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'promotion.created';
    }

    public function broadcastWith(): array
    {
        return [
            'promotion_id' => $this->promotion->id,
            'name' => $this->promotion->name,
            'code' => $this->promotion->code,
            'discount_type' => $this->promotion->discount_type,
            'discount_value' => $this->promotion->discount_value,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
