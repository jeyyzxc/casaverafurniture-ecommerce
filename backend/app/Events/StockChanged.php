<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Product $product,
        public int $oldQuantity,
        public int $newQuantity,
        public string $type = 'update' // 'update', 'low_stock', 'out_of_stock', 'restocked'
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('products'),
            new Channel('admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'stock.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_slug' => $this->product->slug,
            'old_quantity' => $this->oldQuantity,
            'new_quantity' => $this->newQuantity,
            'stock_status' => $this->product->stock_status,
            'is_low_stock' => $this->product->isLowStock(),
            'is_out_of_stock' => $this->product->isOutOfStock(),
            'type' => $this->type,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
