<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::active()
                ->published()
                ->with(['primaryImage', 'category:id,name,slug']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categorySlug = $request->input('category')) {
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                
                $categoryIds = [$category->id];
                $childIds = Category::where('parent_id', $category->id)->pluck('id')->toArray();
                $categoryIds = array_merge($categoryIds, $childIds);
                $query->whereIn('category_id', $categoryIds);
            }
        }

        if ($minPrice = $request->input('min_price')) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice = $request->input('max_price')) {
            $query->where('price', '<=', $maxPrice);
        }

        if ($request->boolean('in_stock')) {
            $query->inStock();
        }

        if ($tags = $request->input('tags')) {
            $tagArray = is_array($tags) ? $tags : explode(',', $tags);
            $query->whereHas('tags', function ($q) use ($tagArray) {
                $q->whereIn('slug', $tagArray);
            });
        }

        if ($material = $request->input('material')) {
            $query->where('material', $material);
        }

        if ($color = $request->input('color')) {
            $query->where('color', $color);
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }
        if ($request->boolean('is_new')) {
            $query->where('is_new', true);
        }
        if ($request->boolean('bestseller')) {
            $query->where('is_bestseller', true);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'newest':
                $query->orderBy('published_at', 'desc');
                break;
            case 'popularity':
                $query->orderBy('order_count', 'desc');
                break;
            case 'rating':
                $query->orderBy('average_rating', 'desc');
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

            $perPage = min($request->input('per_page', 12), 48);
            $products = $query->paginate($perPage);

            $products->getCollection()->transform(function ($product) {
                return $this->formatProduct($product);
            });

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            \Log::error('Products index failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load products',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::active()
            ->published()
            ->where('slug', $slug)
            ->with([
                'images',
                'category',
                'tags',
                'approvedReviews' => function ($q) {
                    $q->with('user:id,first_name,last_name')
                        ->orderBy('created_at', 'desc')
                        ->limit(10);
                },
            ])
            ->firstOrFail();

        $product->increment('view_count');

        $relatedProducts = Product::active()
            ->published()
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('category_id', $product->category_id)
                    ->orWhereHas('tags', function ($tq) use ($product) {
                        $tq->whereIn('tags.id', $product->tags->pluck('id'));
                    });
            })
            ->with(['primaryImage', 'category:id,name,slug'])
            ->limit(4)
            ->get()
            ->map(fn($p) => $this->formatProduct($p));

        return response()->json([
            'success' => true,
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'short_description' => $product->short_description,
                    'description' => $product->description,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'current_price' => $product->current_price,
                    'is_on_sale' => $product->isOnSale(),
                    'stock_status' => $product->stock_status,
                    'stock_quantity' => $product->stock_status === 'in_stock' ? $product->stock_quantity : 0,
                    'is_featured' => $product->is_featured,
                    'is_new' => $product->is_new,
                    'is_bestseller' => $product->is_bestseller,
                    'dimensions' => $product->dimensions,
                    'weight' => $product->weight,
                    'material' => $product->material,
                    'color' => $product->color,
                    'attributes' => $product->attributes,
                    'average_rating' => $product->average_rating,
                    'review_count' => $product->review_count,
                    'images' => $product->images->map(fn($img) => [
                        'id' => $img->id,
                        'image_path' => $img->image_path,
                        'image_url' => $img->image_url,
                        'thumbnail_path' => $img->thumbnail_path,
                        'alt_text' => $img->alt_text,
                        'is_primary' => $img->is_primary,
                    ]),
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                        'slug' => $product->category->slug,
                    ] : null,
                    'tags' => $product->tags->map(fn($tag) => [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                    ]),
                    'reviews' => $product->approvedReviews->map(fn($review) => [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'title' => $review->title,
                        'content' => $review->content,
                        'reviewer_name' => $review->user ? $review->user->first_name : $review->reviewer_name,
                        'is_verified_purchase' => $review->is_verified_purchase,
                        'created_at' => $review->created_at,
                    ]),
                    'meta_title' => $product->meta_title,
                    'meta_description' => $product->meta_description,
                ],
                'related_products' => $relatedProducts,
            ],
        ]);
    }

    public function category(string $slug, Request $request): JsonResponse
    {
        $category = Category::visible()
            ->where('slug', $slug)
            ->with('children')
            ->firstOrFail();

        $categoryIds = [$category->id];
        $childIds = $category->children->pluck('id')->toArray();
        $categoryIds = array_merge($categoryIds, $childIds);

        $query = Product::active()
            ->published()
            ->whereIn('category_id', $categoryIds)
            ->with(['primaryImage']);

        $sortBy = $request->input('sort_by', 'newest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('published_at', 'desc');
                break;
            case 'popularity':
                $query->orderBy('order_count', 'desc');
                break;
        }

        $perPage = min($request->input('per_page', 12), 48);
        $products = $query->paginate($perPage);

        $products->getCollection()->transform(fn($p) => $this->formatProduct($p));

        return response()->json([
            'success' => true,
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image' => $category->image,
                    'subcategories' => $category->children->map(fn($c) => [
                        'id' => $c->id,
                        'name' => $c->name,
                        'slug' => $c->slug,
                    ]),
                ],
                'products' => $products,
            ],
        ]);
    }

    private function formatProduct($product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'current_price' => $product->current_price,
            'is_on_sale' => $product->isOnSale(),
            'stock_status' => $product->stock_status,
            'stock_quantity' => $product->stock_quantity ?? 0,
            'low_stock_threshold' => $product->low_stock_threshold ?? 5,
            'track_inventory' => $product->track_inventory ?? true,
            'is_featured' => $product->is_featured,
            'is_new' => $product->is_new,
            'is_bestseller' => $product->is_bestseller,
            'average_rating' => $product->average_rating,
            'review_count' => $product->review_count,
            'description' => $product->description,
            'attributes' => $product->attributes,
            'image' => $product->primaryImage?->image_url,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
        ];
    }
}
