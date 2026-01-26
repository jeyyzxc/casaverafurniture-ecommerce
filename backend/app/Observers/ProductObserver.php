<?php

namespace App\Observers;

use App\Models\Product;
use App\Events\ProductCreated;
use App\Events\ProductUpdated;
use App\Events\ProductDeleted;
use App\Events\StockChanged;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        // Fire event only if product is active
        if ($product->status === 'active') {
            event(new ProductCreated($product));
        }
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Fire product updated event
        event(new ProductUpdated($product));

        // Check if stock quantity changed
        if ($product->wasChanged('stock_quantity')) {
            $oldQuantity = $product->getOriginal('stock_quantity');
            $newQuantity = $product->stock_quantity;

            // Determine stock change type
            $stockType = 'update';
            if ($product->isLowStock() && !($oldQuantity <= $product->low_stock_threshold)) {
                $stockType = 'low_stock';
            } elseif ($product->isOutOfStock() && $oldQuantity > 0) {
                $stockType = 'out_of_stock';
            } elseif ($newQuantity > $oldQuantity && $oldQuantity <= $product->low_stock_threshold) {
                $stockType = 'restocked';
            }

            // Fire stock changed event
            event(new StockChanged($product, $oldQuantity, $newQuantity, $stockType));
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        // Fire product deleted event
        event(new ProductDeleted($product->id, $product->slug));
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        // Fire product updated event when restored
        event(new ProductUpdated($product));
    }
}
