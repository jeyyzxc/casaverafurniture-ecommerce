<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Promotion;
use App\Events\CartUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Get current cart
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);
        $cart->load(['items.product.primaryImage', 'promotion']);

        return response()->json([
            'success' => true,
            'data' => $this->formatCart($cart),
        ]);
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::active()->published()->findOrFail($validated['product_id']);

        // Check stock
        if ($product->track_inventory && $product->stock_quantity < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => $product->stock_quantity > 0 
                    ? "Only {$product->stock_quantity} items available in stock."
                    : 'This product is out of stock.',
            ], 422);
        }

        $cart = $this->getOrCreateCart($request);

        // Check if item already exists in cart
        $existingItem = $cart->items()->where('product_id', $product->id)->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $validated['quantity'];

            // Check stock for new quantity
            if ($product->track_inventory && $product->stock_quantity < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot add more. Only {$product->stock_quantity} items available.",
                ], 422);
            }

            $existingItem->update([
                'quantity' => $newQuantity,
                'subtotal' => $newQuantity * ($product->current_price),
            ]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $product->price,
                'sale_price' => $product->isOnSale() ? $product->sale_price : null,
                'subtotal' => $validated['quantity'] * $product->current_price,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'product_image' => $product->primaryImage?->image_path,
            ]);
        }

        // Recalculate cart
        $cart->recalculate();
        $cart->load(['items.product.primaryImage', 'promotion']);

        // Broadcast cart updated event
        event(new CartUpdated($cart, 'add'));

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart.',
            'data' => $this->formatCart($cart),
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, CartItem $cartItem): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $cart = $this->getOrCreateCart($request);

        // Verify item belongs to this cart
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart.',
            ], 404);
        }

        $product = $cartItem->product;

        // Check stock
        if ($product && $product->track_inventory && $product->stock_quantity < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => "Only {$product->stock_quantity} items available in stock.",
            ], 422);
        }

        $price = $cartItem->sale_price ?? $cartItem->unit_price;

        $cartItem->update([
            'quantity' => $validated['quantity'],
            'subtotal' => $validated['quantity'] * $price,
        ]);

        // Recalculate cart
        $cart->recalculate();
        $cart->load(['items.product.primaryImage', 'promotion']);

        // Broadcast cart updated event
        event(new CartUpdated($cart, 'update'));

        return response()->json([
            'success' => true,
            'message' => 'Cart updated.',
            'data' => $this->formatCart($cart),
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, CartItem $cartItem): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);

        // Verify item belongs to this cart
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart.',
            ], 404);
        }

        $cartItem->delete();

        // Recalculate cart
        $cart->recalculate();
        $cart->load(['items.product.primaryImage', 'promotion']);

        // Broadcast cart updated event
        event(new CartUpdated($cart, 'remove'));

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
            'data' => $this->formatCart($cart),
        ]);
    }

    /**
     * Clear cart
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);
        $cart->items()->delete();
        $cart->update([
            'item_count' => 0,
            'subtotal' => 0,
            'discount_amount' => 0,
            'total' => 0,
            'promotion_id' => null,
            'coupon_code' => null,
        ]);

        // Broadcast cart updated event
        event(new CartUpdated($cart, 'clear'));

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared.',
            'data' => $this->formatCart($cart),
        ]);
    }

    /**
     * Apply coupon code
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $cart = $this->getOrCreateCart($request);

        if ($cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty.',
            ], 422);
        }

        // Find promotion
        $promotion = Promotion::active()
            ->byCode($validated['code'])
            ->first();

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired coupon code.',
            ], 422);
        }

        // Check if promotion is valid
        if (!$promotion->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'This coupon is no longer valid.',
            ], 422);
        }

        // Check minimum order amount
        if ($promotion->min_order_amount && $cart->subtotal < $promotion->min_order_amount) {
            return response()->json([
                'success' => false,
                'message' => "Minimum order amount of ₱{$promotion->min_order_amount} required.",
            ], 422);
        }

        // Calculate discount
        $discountAmount = $promotion->calculateDiscount($cart->subtotal);

        // Apply to cart
        $cart->update([
            'promotion_id' => $promotion->id,
            'coupon_code' => $promotion->code,
            'discount_amount' => $discountAmount,
            'total' => $cart->subtotal - $discountAmount,
        ]);

        $cart->load(['items.product.primaryImage', 'promotion']);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully.',
            'data' => $this->formatCart($cart),
        ]);
    }

    /**
     * Remove coupon code
     */
    public function removeCoupon(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);

        $cart->update([
            'promotion_id' => null,
            'coupon_code' => null,
            'discount_amount' => 0,
            'total' => $cart->subtotal,
        ]);

        $cart->load(['items.product.primaryImage']);

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed.',
            'data' => $this->formatCart($cart),
        ]);
    }

    /**
     * Get or create cart for user/session
     */
    private function getOrCreateCart(Request $request): Cart
    {
        $user = $request->user();

        if ($user) {
            $cart = Cart::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => $user->id,
                    'status' => 'active',
                    'last_activity_at' => now(),
                ]);
            }

            // Merge guest cart if exists
            $sessionId = $request->header('X-Session-ID') ?? $request->input('session_id');
            if ($sessionId) {
                $guestCart = Cart::where('session_id', $sessionId)
                    ->where('status', 'active')
                    ->whereNull('user_id')
                    ->first();

                if ($guestCart && $guestCart->items->isNotEmpty()) {
                    foreach ($guestCart->items as $item) {
                        $existingItem = $cart->items()->where('product_id', $item->product_id)->first();
                        if ($existingItem) {
                            $existingItem->increment('quantity', $item->quantity);
                        } else {
                            $item->update(['cart_id' => $cart->id]);
                        }
                    }
                    $guestCart->delete();
                    $cart->recalculate();
                }
            }

            return $cart;
        }

        // Guest cart
        $sessionId = $request->header('X-Session-ID') ?? $request->input('session_id') ?? Str::uuid();

        $cart = Cart::where('session_id', $sessionId)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            $cart = Cart::create([
                'session_id' => $sessionId,
                'status' => 'active',
                'last_activity_at' => now(),
            ]);
        }

        return $cart;
    }

    /**
     * Format cart for response
     */
    private function formatCart(Cart $cart): array
    {
        return [
            'id' => $cart->id,
            'item_count' => $cart->item_count,
            'subtotal' => $cart->subtotal,
            'discount_amount' => $cart->discount_amount,
            'total' => $cart->total,
            'coupon_code' => $cart->coupon_code,
            'promotion' => $cart->promotion ? [
                'id' => $cart->promotion->id,
                'name' => $cart->promotion->name,
                'code' => $cart->promotion->code,
                'discount_type' => $cart->promotion->discount_type,
                'discount_value' => $cart->promotion->discount_value,
            ] : null,
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product_sku,
                    'product_image' => $item->product_image ?? $item->product?->primaryImage?->image_path,
                    'product_slug' => $item->product?->slug,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'sale_price' => $item->sale_price,
                    'subtotal' => $item->subtotal,
                    'stock_status' => $item->product?->stock_status ?? 'unknown',
                    'max_quantity' => $item->product?->stock_quantity ?? 99,
                ];
            }),
        ];
    }
}
