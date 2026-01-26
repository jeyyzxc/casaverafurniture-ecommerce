<?php

namespace App\Services;

use App\Models\User;
use App\Models\Admin;
use App\Services\UserNotificationService;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Centralized notification manager that coordinates between
 * user and admin notification services
 */
class NotificationManager
{
    public function __construct(
        private UserNotificationService $userNotificationService,
        private AdminNotificationService $adminNotificationService
    ) {}

    /**
     * Notify both user and admin about order status update
     */
    public function notifyOrderStatusUpdate($order, string $oldStatus, string $newStatus, ?string $trackingNumber = null): void
    {
        DB::beginTransaction();
        try {
            // Notify admin
            $this->adminNotificationService->notifyOrderStatusUpdate($order, $oldStatus, $newStatus);

            // Notify user (if order has a user)
            if ($order->user_id) {
                $trackingInfo = $trackingNumber ? " Tracking number: {$trackingNumber}" : '';
                $this->userNotificationService->notifyOrderStatusUpdate(
                    $order->user,
                    $order,
                    $oldStatus,
                    $newStatus,
                    $trackingInfo
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to send order status notifications', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify both user and admin about new order
     */
    public function notifyNewOrder($order): void
    {
        DB::beginTransaction();
        try {
            // Notify admin
            $this->adminNotificationService->notifyNewOrder($order);

            // Notify user (if order has a user)
            if ($order->user_id) {
                $this->userNotificationService->notifyOrderCreated($order->user, $order);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to send new order notifications', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify user about payment status and admin about payment verification
     */
    public function notifyPaymentStatus($payment, string $status): void
    {
        DB::beginTransaction();
        try {
            // Notify user
            if ($payment->order->user_id) {
                $this->userNotificationService->notifyPaymentStatus(
                    $payment->order->user,
                    $payment->order,
                    $status
                );
            }

            // Admin is already notified via PaymentController
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to send payment status notifications', [
                'payment_id' => $payment->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify all users about product sale
     */
    public function notifyProductSale($product, ?float $salePrice = null): void
    {
        try {
            // Notify admin
            $this->adminNotificationService->notifyProductSale($product, $salePrice);

            // Notify all active users
            $users = User::where('status', 'active')->get();
            
            // Use chunking for large user bases to avoid memory issues
            $users->chunk(100)->each(function ($userChunk) use ($product, $salePrice) {
                DB::transaction(function() use ($userChunk, $product, $salePrice) {
                    foreach ($userChunk as $user) {
                        try {
                            $this->userNotificationService->notifyProductSale($user, $product, $salePrice);
                        } catch (\Exception $e) {
                            Log::warning('Failed to notify user about product sale', [
                                'user_id' => $user->id,
                                'product_id' => $product->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                });
            });
        } catch (\Exception $e) {
            Log::error('Failed to send product sale notifications', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify all users about new featured product
     */
    public function notifyNewProduct($product): void
    {
        try {
            // Notify admin
            $this->adminNotificationService->notifyNewProduct($product);

            // Notify all active users about new featured products
            if ($product->status === 'active' && $product->is_featured) {
                $users = User::where('status', 'active')->get();
                
                $users->chunk(100)->each(function ($userChunk) use ($product) {
                    DB::transaction(function() use ($userChunk, $product) {
                        foreach ($userChunk as $user) {
                            try {
                                $this->userNotificationService->notifyNewProduct($user, $product);
                            } catch (\Exception $e) {
                                Log::warning('Failed to notify user about new product', [
                                    'user_id' => $user->id,
                                    'product_id' => $product->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                    });
                });
            }
        } catch (\Exception $e) {
            Log::error('Failed to send new product notifications', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
