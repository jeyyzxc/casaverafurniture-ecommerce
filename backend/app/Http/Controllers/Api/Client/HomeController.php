<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Collection;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{

    public function index(): JsonResponse
    {

        $banners = Banner::active()
            ->where('position', 'hero')
            ->orderBy('display_order')
            ->get(['id', 'name', 'desktop_image', 'mobile_image', 'alt_text', 'title', 'subtitle', 'button_text', 'link_url']);

        $categories = Category::visible()
            ->mainCategories()
            ->inMenu()
            ->with(['children' => function ($q) {
                $q->visible()->orderBy('display_order');
            }])
            ->orderBy('display_order')
            ->get(['id', 'name', 'slug', 'image', 'icon', 'color']);

        $featuredProducts = Product::active()
            ->published()
            ->featured()
            ->with(['primaryImage', 'category:id,name,slug'])
            ->limit(8)
            ->get();

        $newArrivals = Product::active()
            ->published()
            ->where('is_new', true)
            ->with(['primaryImage', 'category:id,name,slug'])
            ->orderBy('published_at', 'desc')
            ->limit(8)
            ->get();

        $bestsellers = Product::active()
            ->published()
            ->where('is_bestseller', true)
            ->with(['primaryImage', 'category:id,name,slug'])
            ->orderBy('order_count', 'desc')
            ->limit(8)
            ->get();

        $sections = HomepageSection::visible()
            ->ordered()
            ->get();

        $settings = SiteSetting::getPublicSettings();

        return response()->json([
            'success' => true,
            'data' => [
                'banners' => $banners,
                'categories' => $categories,
                'featured_products' => $this->formatProducts($featuredProducts),
                'new_arrivals' => $this->formatProducts($newArrivals),
                'bestsellers' => $this->formatProducts($bestsellers),
                'sections' => $sections,
                'settings' => $settings,
            ],
        ]);
    }

    public function settings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => SiteSetting::getPublicSettings(),
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::visible()
            ->mainCategories()
            ->inMenu()
            ->with(['children' => function ($q) {
                $q->visible()->orderBy('display_order');
            }])
            ->withCount(['products' => function ($q) {
                $q->active()->published();
            }])
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    private function formatProducts($products): array
    {
        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'current_price' => $product->current_price,
                'is_on_sale' => $product->isOnSale(),
                'stock_status' => $product->stock_status,
                'is_featured' => $product->is_featured,
                'is_new' => $product->is_new,
                'is_bestseller' => $product->is_bestseller,
                'average_rating' => $product->average_rating,
                'review_count' => $product->review_count,
                'image' => $product->primaryImage?->image_url,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ] : null,
            ];
        })->toArray();
    }
}
