<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class UserNotificationService
{
    /**
     * Notify user about order status update
     */
    public function notifyOrderStatusUpdate(User $user, Order $order, string $oldStatus, string $newStatus, string $additionalInfo = ''): void
    {
        $message = match($newStatus) {
            'pending' => "Your order #{$order->order_number} has been received and is pending confirmation.",
            'confirmed' => "Your order #{$order->order_number} has been confirmed and is being prepared.",
            'processing' => "Your order #{$order->order_number} is now being processed and prepared for shipment.",
            'shipped' => "Great news! Your order #{$order->order_number} has been shipped.{$additionalInfo}",
            'out_for_delivery' => "Your order #{$order->order_number} is out for delivery and will arrive soon!{$additionalInfo}",
            'delivered' => "Your order #{$order->order_number} has been delivered. We hope you love it!",
            'cancelled' => "Your order #{$order->order_number} has been cancelled.",
            'returned' => "Your order #{$order->order_number} has been returned.",
            'refunded' => "Your order #{$order->order_number} has been refunded.",
            default => "Your order #{$order->order_number} status has been updated to {$newStatus}.{$additionalInfo}",
        };

        $title = match($newStatus) {
            'shipped' => 'Order Shipped',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Order Delivered',
            'cancelled' => 'Order Cancelled',
            'refunded' => 'Order Refunded',
            default => 'Order Status Updated',
        };

        $this->createNotification(
            user: $user,
            title: $title,
            message: $message,
            type: 'order',
            priority: in_array($newStatus, ['cancelled', 'refunded']) ? 'high' : ($newStatus === 'delivered' ? 'high' : 'normal'),
            relatedType: 'order',
            relatedId: $order->id,
            actionUrl: "/orders/{$order->order_number}"
        );
    }

    /**
     * Notify user about new order confirmation
     */
    public function notifyOrderCreated(User $user, Order $order): void
    {
        $itemsCount = $order->items()->count();
        $itemsText = $itemsCount === 1 ? 'item' : 'items';
        
        $this->createNotification(
            user: $user,
            title: 'Order Confirmed',
            message: "Thank you! Your order #{$order->order_number} has been confirmed ({$itemsCount} {$itemsText}, ₱" . number_format($order->total, 2) . ")",
            type: 'order',
            priority: 'high',
            relatedType: 'order',
            relatedId: $order->id,
            actionUrl: "/orders/{$order->order_number}"
        );
    }

    /**
     * Notify user about product sale/promotion
     */
    public function notifyProductSale(User $user, Product $product, ?float $salePrice = null): void
    {
        $originalPrice = $product->price;
        $discount = $salePrice ? ($originalPrice - $salePrice) : 0;
        $discountPercent = $originalPrice > 0 ? round(($discount / $originalPrice) * 100) : 0;
        
        $message = $salePrice 
            ? "🎉 Sale Alert! '{$product->name}' is now on sale for ₱" . number_format($salePrice, 2) . " (was ₱" . number_format($originalPrice, 2) . " - {$discountPercent}% off!)"
            : "Great news! '{$product->name}' is now on sale";

        $this->createNotification(
            user: $user,
            title: '🎉 Product on Sale',
            message: $message,
            type: 'promotion',
            priority: 'high',
            relatedType: 'product',
            relatedId: $product->id,
            actionUrl: "/products/{$product->slug}"
        );
    }

    /**
     * Notify user about new product
     */
    public function notifyNewProduct(User $user, Product $product): void
    {
        $priceText = $product->sale_price 
            ? "Now available for ₱" . number_format($product->sale_price, 2)
            : "Now available for ₱" . number_format($product->price, 2);
        
        $this->createNotification(
            user: $user,
            title: '✨ New Product Available',
            message: "Check out our new product: '{$product->name}' - {$priceText}",
            type: 'product',
            priority: 'normal',
            relatedType: 'product',
            relatedId: $product->id,
            actionUrl: "/products/{$product->slug}"
        );
    }

    /**
     * Notify user about payment status
     */
    public function notifyPaymentStatus(User $user, Order $order, string $status): void
    {
        $message = match($status) {
            'paid' => "Payment for order #{$order->order_number} has been confirmed.",
            'pending' => "Payment for order #{$order->order_number} is pending verification.",
            'failed' => "Payment for order #{$order->order_number} failed. Please try again.",
            default => "Payment status for order #{$order->order_number} has been updated.",
        };

        $this->createNotification(
            user: $user,
            title: 'Payment Status Update',
            message: $message,
            type: 'payment',
            priority: $status === 'failed' ? 'high' : 'normal',
            relatedType: 'order',
            relatedId: $order->id,
            actionUrl: "/orders/{$order->order_number}"
        );
    }

    /**
     * Create a notification for user using Laravel's notification system
     * Uses database transactions for data integrity
     */
    private function createNotification(
        User $user,
        string $title,
        string $message,
        string $type,
        string $priority = 'normal',
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?string $actionUrl = null
    ): void {
        try {
            \DB::transaction(function() use ($user, $title, $message, $type, $priority, $relatedType, $relatedId, $actionUrl) {
                // Use Laravel's notification system (stores in notifications table)
                $user->notify(new \App\Notifications\UserNotification(
                    title: $title,
                    message: $message,
                    type: $type,
                    priority: $priority,
                    relatedType: $relatedType,
                    relatedId: $relatedId,
                    actionUrl: $actionUrl
                ));
            });
        } catch (\Exception $e) {
            \Log::error('Failed to create user notification', [
                'user_id' => $user->id,
                'title' => $title,
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
