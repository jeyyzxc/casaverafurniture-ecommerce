<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Product $product)
    {
        
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('products'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'product.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->product->id,
            'name' => $this->product->name,
            'slug' => $this->product->slug,
            'price' => $this->product->price,
            'sale_price' => $this->product->sale_price,
            'status' => $this->product->status,
            'is_featured' => $this->product->is_featured,
            'is_new' => $this->product->is_new,
            'stock_quantity' => $this->product->stock_quantity,
            'stock_status' => $this->product->stock_status,
            'primary_image' => $this->product->primaryImage?->image_path,
            'category' => $this->product->category ? [
                'id' => $this->product->category->id,
                'name' => $this->product->category->name,
                'slug' => $this->product->category->slug,
            ] : null,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
