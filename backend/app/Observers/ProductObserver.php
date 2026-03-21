<?php

namespace App\Observers;

use App\Models\Product;
use App\Events\ProductCreated;
use App\Events\ProductUpdated;
use App\Events\ProductDeleted;
use App\Events\StockChanged;

class ProductObserver
{
    
    public function created(Product $product): void
    {
        
        if ($product->status === 'active') {
            event(new ProductCreated($product));
        }
    }

    public function updated(Product $product): void
    {
        
        event(new ProductUpdated($product));

        if ($product->wasChanged('stock_quantity')) {
            $oldQuantity = $product->getOriginal('stock_quantity');
            $newQuantity = $product->stock_quantity;

            $stockType = 'update';
            if ($product->isLowStock() && !($oldQuantity <= $product->low_stock_threshold)) {
                $stockType = 'low_stock';
            } elseif ($product->isOutOfStock() && $oldQuantity > 0) {
                $stockType = 'out_of_stock';
            } elseif ($newQuantity > $oldQuantity && $oldQuantity <= $product->low_stock_threshold) {
                $stockType = 'restocked';
            }

            event(new StockChanged($product, $oldQuantity, $newQuantity, $stockType));
        }
    }

    public function deleted(Product $product): void
    {
        
        event(new ProductDeleted($product->id, $product->slug));
    }

    public function restored(Product $product): void
    {
        
        event(new ProductUpdated($product));
    }
}
