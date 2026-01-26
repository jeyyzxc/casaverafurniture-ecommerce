<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $wishlists = Wishlist::where('user_id', $user->id)
            ->with(['product' => function ($q) {
                $q->active()->published()->with(['primaryImage', 'category:id,name,slug']);
            }])
            ->get();

        // Filter out deleted/inactive products
        $items = $wishlists->filter(fn($w) => $w->product !== null)->map(function ($wishlist) {
            $product = $wishlist->product;
            return [
                'id' => $wishlist->id,
                'product_id' => $product->id,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'current_price' => $product->current_price,
                    'is_on_sale' => $product->isOnSale(),
                    'stock_status' => $product->stock_status,
                    'image' => $product->primaryImage?->image_path,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                        'slug' => $product->category->slug,
                    ] : null,
                ],
                'added_at' => $wishlist->created_at,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Add product to wishlist
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        // Check if product is active
        $product = Product::active()->published()->find($validated['product_id']);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        // Check if already in wishlist
        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Product is already in your wishlist.',
            ]);
        }

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $validated['product_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist.',
        ]);
    }

    /**
     * Remove product from wishlist
     */
    public function destroy(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        $deleted = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in wishlist.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist.',
        ]);
    }

    /**
     * Check if product is in wishlist
     */
    public function check(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        $exists = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'in_wishlist' => $exists,
            ],
        ]);
    }

    /**
     * Move item from wishlist to cart
     */
    public function moveToCart(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in wishlist.',
            ], 404);
        }

        // Add to cart using CartController logic
        $cartController = new CartController();
        $request->merge(['product_id' => $productId, 'quantity' => 1]);
        $response = $cartController->addItem($request);

        // Remove from wishlist if added to cart successfully
        $responseData = json_decode($response->getContent(), true);
        if ($responseData['success'] ?? false) {
            $wishlist->delete();
        }

        return $response;
    }
}
