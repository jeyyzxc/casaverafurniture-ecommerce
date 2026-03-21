<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Events\AdminNotificationCreated;
use Illuminate\Support\Facades\DB;

class AdminNotificationService
{
    
    public function createNotification(
        string $title,
        string $message,
        string $type,
        string $priority = 'normal',
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?string $actionUrl = null,
        ?int $adminId = null
    ): AdminNotification {
        try {
            $notification = \DB::transaction(function() use ($title, $message, $type, $priority, $relatedType, $relatedId, $actionUrl, $adminId) {
                return AdminNotification::create([
                    'admin_id' => $adminId, 
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'priority' => $priority,
                    'related_type' => $relatedType,
                    'related_id' => $relatedId,
                    'action_url' => $actionUrl,
                    'icon' => $this->getIconForType($type),
                    'color' => $this->getColorForType($type),
                ]);
            });

            event(new AdminNotificationCreated($notification));

            return $notification;
        } catch (\Exception $e) {
            \Log::error('Failed to create admin notification', [
                'title' => $title,
                'type' => $type,
                'admin_id' => $adminId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function notifyNewOrder(Order $order): void
    {
        $itemsCount = $order->items()->count();
        $itemsText = $itemsCount === 1 ? 'item' : 'items';

        $this->createNotification(
            title: 'New Order Received',
            message: "Order #{$order->order_number} has been placed by {$order->customer_name} ({$itemsCount} {$itemsText}, ₱" . number_format($order->total, 2) . ")",
            type: 'order',
            priority: 'high',
            relatedType: 'order',
            relatedId: $order->id,
            actionUrl: "/admin/orders/{$order->id}"
        );
    }

    public function notifyOrderStatusUpdate(Order $order, string $oldStatus, string $newStatus): void
    {
        $this->createNotification(
            title: 'Order Status Updated',
            message: "Order #{$order->order_number} status changed from {$oldStatus} to {$newStatus}",
            type: 'order',
            priority: $newStatus === 'cancelled' ? 'urgent' : 'normal',
            relatedType: 'order',
            relatedId: $order->id,
            actionUrl: "/admin/orders/{$order->id}"
        );
    }

    public function notifyOrderItemUpdate(OrderItem $orderItem, string $action): void
    {
        $order = $orderItem->order;
        $productName = $orderItem->product_name;

        $messages = [
            'quantity_changed' => "Quantity changed for {$productName} in Order #{$order->order_number}",
            'price_updated' => "Price updated for {$productName} in Order #{$order->order_number}",
            'status_changed' => "Status changed for {$productName} in Order #{$order->order_number}",
        ];

        $this->createNotification(
            title: 'Order Item Updated',
            message: $messages[$action] ?? "Order item updated in Order #{$order->order_number}",
            type: 'order',
            priority: 'normal',
            relatedType: 'order',
            relatedId: $order->id,
            actionUrl: "/admin/orders/{$order->id}"
        );
    }

    public function notifyNewProduct(Product $product): void
    {
        $this->createNotification(
            title: 'New Product Added',
            message: "New product '{$product->name}' has been added to the catalog",
            type: 'product',
            priority: 'normal',
            relatedType: 'product',
            relatedId: $product->id,
            actionUrl: "/admin/products/{$product->id}"
        );
    }

    public function notifyProductSale(Product $product, ?float $salePrice = null): void
    {
        $message = $salePrice
            ? "Product '{$product->name}' is now on sale for ₱" . number_format($salePrice, 2)
            : "Product '{$product->name}' is now on sale";

        $this->createNotification(
            title: 'Product on Sale',
            message: $message,
            type: 'product',
            priority: 'normal',
            relatedType: 'product',
            relatedId: $product->id,
            actionUrl: "/admin/products/{$product->id}"
        );
    }

    public function notifyProductUpdate(Product $product, array $changes = []): void
    {
        $changeMessages = [];

        if (isset($changes['price'])) {
            $changeMessages[] = "Price updated to ₱" . number_format($changes['price'], 2);
        }
        if (isset($changes['stock_quantity'])) {
            $changeMessages[] = "Stock updated to {$changes['stock_quantity']}";
        }
        if (isset($changes['status'])) {
            $changeMessages[] = "Status changed to {$changes['status']}";
        }

        $message = !empty($changeMessages)
            ? "Product '{$product->name}' updated: " . implode(', ', $changeMessages)
            : "Product '{$product->name}' has been updated";

        $this->createNotification(
            title: 'Product Updated',
            message: $message,
            type: 'product',
            priority: 'low',
            relatedType: 'product',
            relatedId: $product->id,
            actionUrl: "/admin/products/{$product->id}"
        );
    }

    public function notifyLowStock(Product $product, int $currentStock): void
    {
        $this->createNotification(
            title: 'Low Stock Alert',
            message: "Product '{$product->name}' is running low ({$currentStock} items remaining)",
            type: 'stock',
            priority: 'high',
            relatedType: 'product',
            relatedId: $product->id,
            actionUrl: "/admin/products/{$product->id}"
        );
    }

    public function notifyOutOfStock(Product $product): void
    {
        $this->createNotification(
            title: 'Out of Stock',
            message: "Product '{$product->name}' is now out of stock",
            type: 'stock',
            priority: 'urgent',
            relatedType: 'product',
            relatedId: $product->id,
            actionUrl: "/admin/products/{$product->id}"
        );
    }

    private function getIconForType(string $type): string
    {
        return match($type) {
            'order' => 'shopping-cart',
            'product' => 'package',
            'stock' => 'alert-triangle',
            'payment' => 'credit-card',
            'user' => 'user',
            'system' => 'info',
            default => 'bell',
        };
    }

    private function getColorForType(string $type): string
    {
        return match($type) {
            'order' => 'blue',
            'product' => 'green',
            'stock' => 'orange',
            'payment' => 'purple',
            'user' => 'indigo',
            'system' => 'gray',
            default => 'blue',
        };
    }

    public function markAsRead(int $notificationId, ?int $adminId = null): bool
    {
        $query = AdminNotification::where('id', $notificationId);

        if ($adminId) {
            $query->where(function($q) use ($adminId) {
                $q->where('admin_id', $adminId)
                  ->orWhereNull('admin_id');
            });
        }

        $notification = $query->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    public function markAllAsRead(?int $adminId = null): int
    {
        $query = AdminNotification::where('is_read', false);

        if ($adminId) {
            $query->where(function($q) use ($adminId) {
                $q->where('admin_id', $adminId)
                  ->orWhereNull('admin_id');
            });
        }

        return $query->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
