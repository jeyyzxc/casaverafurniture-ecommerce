<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $wishlists = Wishlist::where('user_id', $user->id)
            ->with(['product' => function ($q) {
                $q->active()->published()->with(['primaryImage', 'category:id,name,slug']);
            }])
            ->get();

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
                    'image' => $product->primaryImage?->image_url,
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

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $product = Product::active()->published()->find($validated['product_id']);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

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

        $cartController = new CartController();
        $request->merge(['product_id' => $productId, 'quantity' => 1]);
        $response = $cartController->addItem($request);

        $responseData = json_decode($response->getContent(), true);
        if ($responseData['success'] ?? false) {
            $wishlist->delete();
        }

        return $response;
    }
}
