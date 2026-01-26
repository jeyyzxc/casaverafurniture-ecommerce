<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\OrderNote;
use App\Models\ActivityLog;
use App\Events\OrderStatusUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * List all orders with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Load relationships with proper eager loading
            // Load basic relationships first
            $query = Order::with([
                'user:id,first_name,last_name,email',
                'items:id,order_id,product_id,product_name,quantity,unit_price,total',
            ]);
            
            // Load latestPayment separately to handle nullable relationship
            $query->with(['latestPayment' => function ($query) {
                $query->select('id', 'order_id', 'status', 'payment_method_id', 'amount', 'payment_method_name', 'created_at');
            }]);
            
            // Load payment method for latestPayment (only if payment exists)
            $query->with(['latestPayment.paymentMethod' => function ($query) {
                $query->select('id', 'name', 'code');
            }]);

            // Search
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }

            // Filter by payment status
            if ($paymentStatus = $request->input('payment_status')) {
                $query->where('payment_status', $paymentStatus);
            }

            // Filter by date range
            if ($startDate = $request->input('start_date')) {
                $query->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate = $request->input('end_date')) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            // Sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->input('per_page', 15);
            
            // Get orders with pagination
            $orders = $query->paginate($perPage);

            // Ensure payment methods are loaded for orders that have payments
            $orders->getCollection()->each(function ($order) {
                if ($order->latestPayment && $order->latestPayment->payment_method_id) {
                    // Load payment method if not already loaded
                    if (!$order->latestPayment->relationLoaded('paymentMethod')) {
                        try {
                            $order->latestPayment->load('paymentMethod:id,name,code');
                        } catch (\Exception $e) {
                            \Log::warning('Failed to load payment method for order', [
                                'order_id' => $order->id,
                                'payment_id' => $order->latestPayment->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            });

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Admin OrderController index failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load orders.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Admin OrderController index failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load orders.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get single order
     */
    public function show(Order $order): JsonResponse
    {
        $order->load([
            'user:id,first_name,last_name,email,phone',
            'items.product:id,name,slug',
            'payments.paymentMethod',
            'statusHistory.admin:id,first_name,last_name',
            'notes.admin:id,first_name,last_name',
            'shippingZone',
            'courier',
        ]);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'returned', 'refunded'])],
            'comment' => ['nullable', 'string', 'max:500'],
            'notify_customer' => ['boolean'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
        ]);

        $previousStatus = $order->status;

        if ($previousStatus === $validated['status']) {
            return response()->json([
                'success' => false,
                'message' => 'Order is already in this status.',
            ], 422);
        }

        // Update order
        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'shipped') {
            $updateData['shipped_at'] = now();
            if (!empty($validated['tracking_number'])) {
                $updateData['tracking_number'] = $validated['tracking_number'];
            }
        } elseif ($validated['status'] === 'delivered') {
            $updateData['delivered_at'] = now();
        } elseif ($validated['status'] === 'cancelled') {
            $updateData['cancelled_at'] = now();
        }

        $order->update($updateData);

        // Create status history
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $validated['status'],
            'previous_status' => $previousStatus,
            'comment' => $validated['comment'] ?? null,
            'changed_by_type' => 'admin',
            'admin_id' => auth()->id(),
            'is_customer_notified' => $validated['notify_customer'] ?? false,
        ]);

        // Log activity
        ActivityLog::log('status_update', 'orders', "Updated order {$order->order_number} status from {$previousStatus} to {$validated['status']}", $order);

        // Broadcast order status updated event
        event(new OrderStatusUpdated($order, $previousStatus, $validated['status']));

        // TODO: Send notification to customer if notify_customer is true

        $order->load('statusHistory');

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'data' => $order,
        ]);
    }

    /**
     * Add note to order
     */
    public function addNote(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
            'is_private' => ['boolean'],
        ]);

        $note = OrderNote::create([
            'order_id' => $order->id,
            'admin_id' => auth()->id(),
            'note' => $validated['note'],
            'is_private' => $validated['is_private'] ?? true,
        ]);

        $note->load('admin:id,first_name,last_name');

        return response()->json([
            'success' => true,
            'message' => 'Note added successfully.',
            'data' => $note,
        ]);
    }

    /**
     * Update shipping info
     */
    public function updateShipping(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'courier_id' => ['nullable', 'exists:couriers,id'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'tracking_url' => ['nullable', 'url', 'max:255'],
            'estimated_delivery_date' => ['nullable', 'date'],
        ]);

        $order->update($validated);

        ActivityLog::log('update', 'orders', "Updated shipping info for order {$order->order_number}", $order);

        return response()->json([
            'success' => true,
            'message' => 'Shipping information updated successfully.',
            'data' => $order,
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        if (!$order->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be cancelled in its current status.',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $previousStatus = $order->status;

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['reason'],
        ]);

        // Create status history
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'cancelled',
            'previous_status' => $previousStatus,
            'comment' => $validated['reason'],
            'changed_by_type' => 'admin',
            'admin_id' => auth()->id(),
        ]);

        // Restore stock
        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->incrementStock($item->quantity);
            }
        }

        ActivityLog::log('cancel', 'orders', "Cancelled order {$order->order_number}", $order);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
        ]);
    }

    /**
     * Get order statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
