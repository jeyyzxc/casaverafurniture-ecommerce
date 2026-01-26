<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Promotion;
use App\Models\ShippingZone;
use App\Models\PromotionUsage;
use App\Events\OrderCreated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private function debugLog($location, $message, $data, $hypothesisId = 'A') {
        $logPath = base_path('.cursor/debug.log');
        @file_put_contents($logPath, json_encode([
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => time() * 1000,
            'location' => $location,
            'message' => $message,
            'data' => $data,
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => $hypothesisId
        ]) . "\n", FILE_APPEND | LOCK_EX);
    }
    /**
     * List user's orders
     */
    public function index(Request $request): JsonResponse
    {
        // #region agent log
        $this->debugLog('OrderController.php:27', 'index() entry', ['user_id' => $request->user()?->id, 'user_type' => get_class($request->user())], 'B');
        // #endregion
        try {
            $user = $request->user();
            
            // Ensure user is a User model, not Admin
            if (!$user instanceof \App\Models\User) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user type. Please log in as a customer.',
                ], 403);
            }
            // #region agent log
            $this->debugLog('OrderController.php:30', 'Before query build', ['user_id' => $user?->id], 'B');
            // #endregion

            $query = Order::where('user_id', $user->id)
                ->with(['items', 'latestPayment.paymentMethod']);

            // Filter by status
            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }

            $perPage = $request->input('per_page', 10);
            // #region agent log
            $this->debugLog('OrderController.php:38', 'Before paginate', ['per_page' => $perPage], 'B');
            // #endregion
            $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);
            // #region agent log
            $this->debugLog('OrderController.php:39', 'After paginate', ['orders_count' => count($orders->items()), 'first_order_id' => $orders->items()[0]->id ?? null], 'B');
            // #endregion
            // #region agent log
            if(count($orders->items()) > 0) {
                $firstOrder = $orders->items()[0];
                $this->debugLog('OrderController.php:42', 'Checking first order relationships', [
                    'order_id' => $firstOrder->id,
                    'has_items' => isset($firstOrder->items),
                    'items_count' => $firstOrder->items->count() ?? 0,
                    'has_latest_payment' => isset($firstOrder->latestPayment),
                    'payment_method_id' => $firstOrder->latestPayment->payment_method_id ?? null
                ], 'B');
            }
            // #endregion

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            // #region agent log
            $this->debugLog('OrderController.php:45', 'index() exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ], 'B');
            // #endregion
            \Log::error('Order index failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
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
    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();

        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $user->id)
            ->with([
                'items',
                'payments.paymentMethod',
                'statusHistory',
                'shippingZone',
                'courier',
            ])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Create order (checkout)
     */
    public function store(Request $request): JsonResponse
    {
        // #region agent log
        $this->debugLog('OrderController.php:75', 'store() entry', ['user_id' => $request->user()?->id, 'user_type' => get_class($request->user())], 'A');
        // #endregion
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required. Please log in to continue.',
            ], 401);
        }
        
        // Ensure user is a User model, not Admin
        if (!$user instanceof \App\Models\User) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user type. Please log in as a customer.',
            ], 403);
        }

        // #region agent log
        $this->debugLog('OrderController.php:85', 'Before validation', [
            'user_id' => $user->id,
            'has_cart' => Cart::where('user_id', $user->id)->where('status', 'active')->exists()
        ], 'A');
        // #endregion
        // #region agent log
        $logPath = base_path('.cursor/debug.log');
        @file_put_contents($logPath, json_encode([
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => time() * 1000,
            'location' => 'OrderController.php:164',
            'message' => 'Before validation',
            'data' => ['request_keys' => array_keys($request->all()), 'request_data' => $request->all()],
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'B'
        ]) . "\n", FILE_APPEND | LOCK_EX);
        // #endregion
        
        try {
            $validated = $request->validate([
                // Address selection (either use saved address ID or provide full address)
                'shipping_address_id' => ['nullable', 'exists:user_addresses,id'],
                'billing_address_id' => ['nullable', 'exists:user_addresses,id'],
                'billing_same_as_shipping' => ['boolean'],
                
                // Full address details (required if address_id not provided)
                'shipping_address_line_1' => ['required_without:shipping_address_id', 'nullable', 'string', 'max:255'],
                'shipping_address_line_2' => ['nullable', 'string', 'max:255'],
                'shipping_city' => ['required_without:shipping_address_id', 'nullable', 'string', 'max:100'],
                'shipping_province' => ['required_without:shipping_address_id', 'nullable', 'string', 'max:100'],
                'shipping_postal_code' => ['required_without:shipping_address_id', 'nullable', 'string', 'max:20'],
                'shipping_name' => ['required_without:shipping_address_id', 'nullable', 'string', 'max:100'],
                'shipping_phone' => ['required_without:shipping_address_id', 'nullable', 'string', 'max:20'],
                
                'billing_address_line_1' => ['exclude_if:billing_same_as_shipping,true', 'required_without:billing_address_id', 'nullable', 'string', 'max:255'],
                'billing_address_line_2' => ['nullable', 'string', 'max:255'],
                'billing_city' => ['exclude_if:billing_same_as_shipping,true', 'required_without:billing_address_id', 'nullable', 'string', 'max:100'],
                'billing_province' => ['exclude_if:billing_same_as_shipping,true', 'required_without:billing_address_id', 'nullable', 'string', 'max:100'],
                'billing_postal_code' => ['exclude_if:billing_same_as_shipping,true', 'required_without:billing_address_id', 'nullable', 'string', 'max:20'],
                
                'shipping_zone_id' => ['required', 'exists:shipping_zones,id'],
                'payment_method_id' => ['required', 'exists:payment_methods,id'],
                
                // Payment confirmation details
                'payment_confirmation' => ['nullable', 'array'],
                'payment_confirmation.sender_name' => ['nullable', 'string', 'max:200'],
                'payment_confirmation.sender_account' => ['nullable', 'string', 'max:100'],
                'payment_confirmation.reference_number' => ['nullable', 'string', 'max:100'],
                'payment_confirmation.payment_date' => ['nullable', 'date'],
                'payment_confirmation.proof_image' => ['nullable', 'string'], // Base64 encoded image
                'payment_confirmation.card_number' => ['nullable', 'string', 'max:20'], // Last 4 digits for bank cards
                'payment_confirmation.card_holder_name' => ['nullable', 'string', 'max:200'],
                'payment_confirmation.card_expiry' => ['nullable', 'string', 'max:10'],
                'payment_confirmation.card_cvv' => ['nullable', 'string', 'max:4'],
                
                'notes' => ['nullable', 'string', 'max:500'],
            ]);
            
            // #region agent log
            @file_put_contents($logPath, json_encode([
                'id' => 'log_' . time() . '_' . uniqid(),
                'timestamp' => time() * 1000,
                'location' => 'OrderController.php:200',
                'message' => 'Validation passed',
                'data' => ['validated_keys' => array_keys($validated), 'has_shipping_address_id' => isset($validated['shipping_address_id'])],
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'B'
            ]) . "\n", FILE_APPEND | LOCK_EX);
            // #endregion
        } catch (\Illuminate\Validation\ValidationException $e) {
            // #region agent log
            @file_put_contents($logPath, json_encode([
                'id' => 'log_' . time() . '_' . uniqid(),
                'timestamp' => time() * 1000,
                'location' => 'OrderController.php:210',
                'message' => 'Validation failed',
                'data' => ['errors' => $e->errors(), 'request_data' => $request->all()],
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'B'
            ]) . "\n", FILE_APPEND | LOCK_EX);
            // #endregion
            
            throw $e;
        }
        // #region agent log
        $this->debugLog('OrderController.php:103', 'After validation', [
            'validated_keys' => array_keys($validated),
            'shipping_zone_id' => $validated['shipping_zone_id'] ?? null,
            'payment_method_id' => $validated['payment_method_id'] ?? null
        ], 'E');
        // #endregion

        // Get cart - ensure items are loaded
        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['items.product', 'items'])
            ->first();

        if (!$cart) {
            // Try to merge guest cart if session ID exists
            $sessionId = $request->header('X-Session-ID') ?? $request->input('session_id');
            if ($sessionId) {
                $guestCart = Cart::where('session_id', $sessionId)
                    ->where('status', 'active')
                    ->whereNull('user_id')
                    ->with('items')
                    ->first();
                
                if ($guestCart && $guestCart->items->isNotEmpty()) {
                    // Create user cart and merge items
                    $cart = Cart::create([
                        'user_id' => $user->id,
                        'status' => 'active',
                        'last_activity_at' => now(),
                    ]);
                    
                    foreach ($guestCart->items as $item) {
                        $item->update(['cart_id' => $cart->id]);
                    }
                    
                    $guestCart->delete();
                    $cart->recalculate();
                    $cart->load(['items.product', 'items']);
                }
            }
            
            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty. Please add items to your cart before checkout.',
                ], 422);
            }
        }

        // Reload items to ensure they're fresh
        $cart->load('items.product');
        
        // Check if cart has items
        if ($cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty. Please add items to your cart before checkout.',
            ], 422);
        }

        // Ensure cart is recalculated
        $cart->recalculate();
        $cart->refresh();

        // Validate cart subtotal
        if (!$cart->subtotal || $cart->subtotal <= 0) {
            \Log::error('Cart subtotal is invalid', [
                'user_id' => $user->id,
                'cart_id' => $cart->id,
                'subtotal' => $cart->subtotal,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Cart subtotal is invalid. Please refresh your cart and try again.',
            ], 422);
        }

        // Log cart info for debugging
        \Log::info('Order creation attempt', [
            'user_id' => $user->id,
            'cart_id' => $cart->id,
            'cart_items_count' => $cart->items->count(),
            'cart_subtotal' => $cart->subtotal,
            'cart_discount' => $cart->discount_amount ?? 0,
        ]);

        // Validate stock
        foreach ($cart->items as $item) {
            if (!$item->product || $item->product->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => "Product '{$item->product_name}' is no longer available.",
                ], 422);
            }
            if ($item->product->track_inventory && $item->product->stock_quantity < $item->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for '{$item->product_name}'.",
                ], 422);
            }
        }

        // Get shipping zone and validate it's active
        $shippingZone = ShippingZone::active()->findOrFail($validated['shipping_zone_id']);
        
        if (!$shippingZone->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Selected shipping zone is not available.',
            ], 422);
        }
        
        // Calculate shipping amount based on cart subtotal and zone settings
        $shippingAmount = $cart->subtotal >= ($shippingZone->free_shipping_threshold ?? PHP_INT_MAX) 
            ? 0 
            : $shippingZone->base_rate;

        // Calculate base total (before payment fee)
        $baseTotal = $cart->subtotal - $cart->discount_amount + $shippingAmount;

        // Get payment method early to calculate fee
        $paymentMethod = PaymentMethod::find($validated['payment_method_id']);
        
        if (!$paymentMethod) {
            return response()->json([
                'success' => false,
                'message' => 'Selected payment method not found.',
            ], 422);
        }
        
        if (!$paymentMethod->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Selected payment method is no longer available.',
            ], 422);
        }

        // Calculate payment fee based on base total
        $paymentFee = $paymentMethod->calculateFee($baseTotal);
        
        // Final total includes payment fee
        $finalTotal = $baseTotal + $paymentFee;

        // Handle saved addresses
        $shippingAddress = null;
        $billingAddress = null;
        
        if (!empty($validated['shipping_address_id'])) {
            $shippingAddress = \App\Models\UserAddress::where('user_id', $user->id)
                ->where('id', $validated['shipping_address_id'])
                ->first();
            
            if (!$shippingAddress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected shipping address not found.',
                ], 422);
            }
        }
        
        if (!empty($validated['billing_address_id'])) {
            $billingAddress = \App\Models\UserAddress::where('user_id', $user->id)
                ->where('id', $validated['billing_address_id'])
                ->first();
            
            if (!$billingAddress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected billing address not found.',
                ], 422);
            }
        }
        
        // Prepare shipping address data
        if ($shippingAddress) {
            $shippingData = [
                'shipping_name' => $shippingAddress->recipient_name,
                'shipping_address_line_1' => $shippingAddress->address_line_1,
                'shipping_address_line_2' => $shippingAddress->address_line_2,
                'shipping_city' => $shippingAddress->city,
                'shipping_province' => $shippingAddress->province,
                'shipping_postal_code' => $shippingAddress->postal_code,
                'shipping_phone' => $shippingAddress->phone,
            ];
        } else {
            $shippingData = [
                'shipping_name' => $validated['shipping_name'],
                'shipping_address_line_1' => $validated['shipping_address_line_1'],
                'shipping_address_line_2' => $validated['shipping_address_line_2'] ?? null,
                'shipping_city' => $validated['shipping_city'],
                'shipping_province' => $validated['shipping_province'],
                'shipping_postal_code' => $validated['shipping_postal_code'],
                'shipping_phone' => $validated['shipping_phone'],
            ];
        }
        
        // Billing address
        $billingSameAsShipping = $validated['billing_same_as_shipping'] ?? true;
        
        // #region agent log
        @file_put_contents($logPath, json_encode([
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => time() * 1000,
            'location' => 'OrderController.php:330',
            'message' => 'Billing address logic',
            'data' => [
                'billing_same_as_shipping' => $billingSameAsShipping,
                'has_billing_address_id' => !empty($validated['billing_address_id']),
                'has_shipping_address_id' => !empty($validated['shipping_address_id']),
                'has_billing_address' => $billingAddress !== null
            ],
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'B'
        ]) . "\n", FILE_APPEND | LOCK_EX);
        // #endregion
        
        if ($billingSameAsShipping) {
            // Map shipping data to billing data
            $billingData = [
                'billing_name' => $shippingData['shipping_name'],
                'billing_address_line_1' => $shippingData['shipping_address_line_1'],
                'billing_address_line_2' => $shippingData['shipping_address_line_2'] ?? null,
                'billing_city' => $shippingData['shipping_city'],
                'billing_province' => $shippingData['shipping_province'],
                'billing_postal_code' => $shippingData['shipping_postal_code'],
                'billing_phone' => $shippingData['shipping_phone'],
            ];
        } elseif ($billingAddress) {
            $billingData = [
                'billing_name' => $billingAddress->recipient_name,
                'billing_address_line_1' => $billingAddress->address_line_1,
                'billing_address_line_2' => $billingAddress->address_line_2,
                'billing_city' => $billingAddress->city,
                'billing_province' => $billingAddress->province,
                'billing_postal_code' => $billingAddress->postal_code,
                'billing_phone' => $billingAddress->phone,
            ];
        } else {
            $billingData = [
                'billing_name' => $validated['billing_name'] ?? $shippingData['shipping_name'],
                'billing_address_line_1' => $validated['billing_address_line_1'] ?? $shippingData['shipping_address_line_1'],
                'billing_address_line_2' => $validated['billing_address_line_2'] ?? null,
                'billing_city' => $validated['billing_city'] ?? $shippingData['shipping_city'],
                'billing_province' => $validated['billing_province'] ?? $shippingData['shipping_province'],
                'billing_postal_code' => $validated['billing_postal_code'] ?? $shippingData['shipping_postal_code'],
                'billing_phone' => $validated['billing_phone'] ?? $shippingData['shipping_phone'],
            ];
        }
        
        // #region agent log
        $this->debugLog('OrderController.php:243', 'Before transaction', [
            'cart_id' => $cart->id ?? null,
            'cart_subtotal' => $cart->subtotal ?? null,
            'cart_items_count' => $cart->items->count() ?? 0,
            'shipping_zone_id' => $validated['shipping_zone_id'],
            'payment_method_id' => $validated['payment_method_id'],
            'has_shipping_address_id' => !empty($validated['shipping_address_id']),
            'has_billing_address_id' => !empty($validated['billing_address_id']),
        ], 'E');
        // #endregion

        DB::beginTransaction();

        try {
            // Ensure user has required fields
            $customerName = $user->full_name ?? ($user->first_name . ' ' . $user->last_name) ?? 'Customer';
            $customerEmail = $user->email ?? '';
            $customerPhone = $user->phone ?? null;
            
            if (empty($customerEmail)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'User email is required. Please update your profile.',
                ], 422);
            }
            // #region agent log
            $this->debugLog('OrderController.php:260', 'Before Order::create', [
                'customer_email' => $customerEmail,
                'shipping_zone_id' => $validated['shipping_zone_id'],
                'payment_method_id' => $validated['payment_method_id']
            ], 'A');
            // #endregion

            // #region agent log
            @file_put_contents($logPath, json_encode([
                'id' => 'log_' . time() . '_' . uniqid(),
                'timestamp' => time() * 1000,
                'location' => 'OrderController.php:500',
                'message' => 'Before Order::create',
                'data' => [
                    'shipping_data' => $shippingData,
                    'billing_data' => $billingData,
                    'customer_email' => $customerEmail,
                    'customer_name' => $customerName
                ],
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'B'
            ]) . "\n", FILE_APPEND | LOCK_EX);
            // #endregion
            
            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'customer_email' => $customerEmail,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'status' => 'pending',
                'billing_name' => $billingData['billing_name'],
                'billing_address_line_1' => $billingData['billing_address_line_1'],
                'billing_address_line_2' => $billingData['billing_address_line_2'] ?? null,
                'billing_city' => $billingData['billing_city'],
                'billing_province' => $billingData['billing_province'],
                'billing_postal_code' => $billingData['billing_postal_code'],
                'billing_country' => 'Philippines',
                'billing_phone' => $billingData['billing_phone'],
                'shipping_name' => $shippingData['shipping_name'],
                'shipping_address_line_1' => $shippingData['shipping_address_line_1'],
                'shipping_address_line_2' => $shippingData['shipping_address_line_2'] ?? null,
                'shipping_city' => $shippingData['shipping_city'],
                'shipping_province' => $shippingData['shipping_province'],
                'shipping_postal_code' => $shippingData['shipping_postal_code'],
                'shipping_country' => 'Philippines',
                'shipping_phone' => $shippingData['shipping_phone'],
                'subtotal' => $cart->subtotal ?? 0,
                'discount_amount' => $cart->discount_amount ?? 0,
                'shipping_amount' => $shippingAmount,
                'tax_amount' => 0, // Can be calculated later if needed
                'total' => $finalTotal, // Total including payment fee
                'currency' => 'PHP',
                'promotion_id' => $cart->promotion_id,
                'coupon_code' => $cart->coupon_code,
                'shipping_zone_id' => $shippingZone->id,
                'shipping_method' => $shippingZone->name,
                'estimated_delivery_date' => now()->addDays($shippingZone->max_delivery_days ?? 7),
                'payment_status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'source' => 'web',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            // #region agent log
            $this->debugLog('OrderController.php:299', 'After Order::create', [
                'order_id' => $order->id ?? null,
                'order_number' => $order->order_number ?? null
            ], 'A');
            // #endregion

            // Create order items
            foreach ($cart->items as $item) {
                // Reload product to ensure we have latest data
                $product = $item->product;
                
                if (!$product) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Product '{$item->product_name}' is no longer available.",
                    ], 422);
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product_sku ?? $product->sku,
                    'product_image' => $item->product_image,
                    'product_description' => $product->short_description ?? null,
                    'product_options' => $item->options ?? null,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price ?? $product->price,
                    'sale_price' => $item->sale_price,
                    'discount_amount' => 0, // Can be calculated if needed
                    'tax_amount' => 0, // Can be calculated if needed
                    'subtotal' => $item->subtotal,
                    'total' => $item->subtotal,
                    'status' => 'pending',
                ]);

                // Decrement stock
                if ($product->track_inventory) {
                    $product->decrementStock($item->quantity);
                }

                // Update product order count
                $product->increment('order_count');
            }
            // #region agent log
            $this->debugLog('OrderController.php:339', 'After OrderItem creation', [
                'items_created' => $cart->items->count()
            ], 'A');
            // #endregion

            // Create status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'notes' => 'Order placed',
                'changed_by_type' => 'customer',
            ]);

            // Record promotion usage
            if ($cart->promotion_id) {
                PromotionUsage::create([
                    'promotion_id' => $cart->promotion_id,
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'discount_amount' => $cart->discount_amount,
                    'code_used' => $cart->coupon_code,
                ]);

                // Increment promotion used count (reload promotion to ensure it exists)
                $promotion = Promotion::find($cart->promotion_id);
                if ($promotion) {
                    $promotion->increment('used_count');
                }
            }

            // Create payment record (payment method already validated above)
            try {
                // Prepare payment details from confirmation
                $paymentDetails = [];
                $proofImage = null;
                if (!empty($validated['payment_confirmation'])) {
                    $confirmation = $validated['payment_confirmation'];
                    $paymentDetails = [
                        'sender_name' => $confirmation['sender_name'] ?? null,
                        'sender_account' => $confirmation['sender_account'] ?? null,
                        'reference_number' => $confirmation['reference_number'] ?? null,
                        'payment_date' => $confirmation['payment_date'] ?? null,
                    ];
                    
                    // Store proof image if provided (base64 encoded)
                    if (!empty($confirmation['proof_image'])) {
                        $proofImage = $confirmation['proof_image'];
                    }
                    
                    // Add bank card details if provided
                    if (!empty($confirmation['card_number'])) {
                        $paymentDetails['card'] = [
                            'last_four' => substr($confirmation['card_number'], -4),
                            'holder_name' => $confirmation['card_holder_name'] ?? null,
                            'expiry' => $confirmation['card_expiry'] ?? null,
                            // Note: CVV is never stored for security
                        ];
                    }
                }
                
                // #region agent log
                $this->debugLog('OrderController.php:368', 'Before Payment::create', [
                    'order_id' => $order->id,
                    'payment_method_id' => $validated['payment_method_id'],
                    'payment_method_name' => $paymentMethod->name ?? null,
                    'final_total' => $finalTotal,
                    'payment_fee' => $paymentFee,
                    'has_payment_confirmation' => !empty($validated['payment_confirmation'])
                ], 'A');
                // #endregion
                $payment = Payment::create([
                    'transaction_id' => Payment::generateTransactionId(),
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'payment_method_id' => $validated['payment_method_id'],
                    'payment_method_name' => $paymentMethod->name,
                    'amount' => $finalTotal, // Total including fee
                    'fee_amount' => $paymentFee,
                    'net_amount' => $baseTotal, // Amount before fee (what merchant receives)
                    'status' => 'pending',
                    'currency' => 'PHP',
                    'payment_details' => !empty($paymentDetails) ? $paymentDetails : null,
                    'sender_name' => $paymentDetails['sender_name'] ?? null,
                    'sender_account' => $paymentDetails['sender_account'] ?? null,
                    'reference_number' => $paymentDetails['reference_number'] ?? null,
                    'payment_date' => !empty($paymentDetails['payment_date']) ? $paymentDetails['payment_date'] : null,
                    'proof_image' => $proofImage, // Base64 encoded proof image
                ]);
                // #region agent log
                $this->debugLog('OrderController.php:379', 'After Payment::create', [
                    'payment_id' => $payment->id ?? null,
                    'transaction_id' => $payment->transaction_id ?? null
                ], 'A');
                // #endregion
            } catch (\Exception $e) {
                // #region agent log
                $this->debugLog('OrderController.php:381', 'Payment::create exception', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ], 'A');
                // #endregion
                DB::rollBack();
                \Log::error('Failed to create payment record', [
                    'order_id' => $order->id ?? null,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment record. Please try again.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }

            // Update user stats (only if user is a User model, not Admin)
            if ($user instanceof \App\Models\User) {
                $user->increment('order_count');
                $user->update(['last_order_at' => now()]);
            }

            // Mark cart as converted
            $cart->update([
                'status' => 'converted',
                'converted_at' => now(),
            ]);

            DB::commit();
            // #region agent log
            $this->debugLog('OrderController.php:459', 'Transaction committed', ['order_id' => $order->id], 'A');
            // #endregion

            // Reload order with relationships
            // #region agent log
            $this->debugLog('OrderController.php:462', 'Before load relationships', ['order_id' => $order->id], 'B');
            // #endregion
            $order->load(['items', 'latestPayment.paymentMethod']);
            // #region agent log
            $this->debugLog('OrderController.php:463', 'After load relationships', [
                'order_id' => $order->id,
                'items_count' => $order->items->count() ?? 0,
                'has_payment' => !is_null($order->latestPayment)
            ], 'B');
            // #endregion

            // Broadcast order created event for real-time updates (wrap in try-catch to prevent failures)
            try {
                event(new OrderCreated($order));
            } catch (\Exception $e) {
                // Log but don't fail the order creation if broadcasting fails
                \Log::warning('Failed to broadcast OrderCreated event', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully.',
                'data' => [
                    'order' => $order,
                    'payment_instructions' => $paymentMethod->payment_instructions ?? null,
                    'payment_details' => $paymentMethod->account_details ?? null,
                ],
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            // #region agent log
            $this->debugLog('OrderController.php:430', 'QueryException caught', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'sql' => method_exists($e, 'getSql') ? $e->getSql() : 'N/A',
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 'A');
            // #endregion
            
            // Log database errors with more context
            \Log::error('Order creation failed - Database error', [
                'user_id' => $user->id ?? null,
                'cart_id' => $cart->id ?? null,
                'error' => $e->getMessage(),
                'sql' => method_exists($e, 'getSql') ? $e->getSql() : 'N/A',
                'bindings' => method_exists($e, 'getBindings') ? $e->getBindings() : [],
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Provide more specific error messages for common database errors
            $errorMessage = 'Failed to create order due to a database error. Please try again.';
            $errorDetails = null;
            
            if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                $errorMessage = 'Order creation failed due to data integrity issue. Please refresh and try again.';
            } elseif (str_contains($e->getMessage(), 'Column cannot be null')) {
                $errorMessage = 'Order creation failed due to missing required information. Please check your form and try again.';
                // Extract which column is null
                if (preg_match("/Column '([^']+)' cannot be null/", $e->getMessage(), $matches)) {
                    $errorDetails = "Missing required field: {$matches[1]}";
                }
            } elseif (str_contains($e->getMessage(), "Unknown column")) {
                $errorMessage = 'Database schema mismatch detected. Please contact support.';
                if (preg_match("/Unknown column '([^']+)'/", $e->getMessage(), $matches)) {
                    $errorDetails = "Unknown column: {$matches[1]}";
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => config('app.debug') ? $e->getMessage() : null,
                'details' => config('app.debug') ? $errorDetails : null,
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            // #region agent log
            $logPath = base_path('.cursor/debug.log');
            @file_put_contents($logPath, json_encode([
                'id' => 'log_' . time() . '_' . uniqid(),
                'timestamp' => time() * 1000,
                'location' => 'OrderController.php:821',
                'message' => 'General Exception caught',
                'data' => [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'class' => get_class($e),
                    'trace' => substr($e->getTraceAsString(), 0, 1000)
                ],
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'B'
            ]) . "\n", FILE_APPEND | LOCK_EX);
            // #endregion
            
            // Log the full error for debugging
            \Log::error('Order creation failed - General exception', [
                'user_id' => $user->id ?? null,
                'cart_id' => $cart->id ?? null,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'file' => config('app.debug') ? $e->getFile() . ':' . $e->getLine() : null,
            ], 500);
        }
    }

    /**
     * Submit payment proof
     */
    public function submitPayment(Request $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();

        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $user->id)
            ->with('latestPayment')
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been paid.',
            ], 422);
        }

        $validated = $request->validate([
            'sender_name' => ['required', 'string', 'max:100'],
            'sender_account' => ['nullable', 'string', 'max:50'],
            'reference_number' => ['required', 'string', 'max:100'],
            'payment_date' => ['required', 'date'],
            'proof_image' => ['nullable', 'string'], // Base64 encoded image
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $payment = $order->latestPayment;

        if ($payment) {
            $payment->update([
                'sender_name' => $validated['sender_name'],
                'sender_account' => $validated['sender_account'] ?? null,
                'reference_number' => $validated['reference_number'],
                'payment_date' => $validated['payment_date'],
                'proof_image' => $validated['proof_image'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending_verification',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment submitted for verification.',
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();

        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (!$order->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be cancelled.',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $previousStatus = $order->status;

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['reason'] ?? 'Cancelled by customer',
        ]);

        // Restore stock
        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->incrementStock($item->quantity);
            }
        }

        // Create status history
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'cancelled',
            'previous_status' => $previousStatus,
            'notes' => $validated['reason'] ?? 'Cancelled by customer',
            'changed_by_type' => 'customer',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
        ]);
    }

    /**
     * Get available shipping zones
     */
    public function shippingZones(): JsonResponse
    {
        $zones = ShippingZone::active()
            ->select('id', 'name', 'type', 'description', 'regions', 'base_rate', 'free_shipping_threshold', 'min_delivery_days', 'max_delivery_days')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $zones,
        ]);
    }

    /**
     * Get available payment methods
     */
    public function paymentMethods(): JsonResponse
    {
        $methods = PaymentMethod::active()
            ->select('id', 'name', 'code', 'type', 'description', 'icon', 'payment_instructions', 'account_details', 'fee_fixed', 'fee_percentage', 'requires_verification', 'requires_proof_of_payment', 'min_amount', 'max_amount', 'is_active', 'display_order')
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $methods,
        ]);
    }
}
