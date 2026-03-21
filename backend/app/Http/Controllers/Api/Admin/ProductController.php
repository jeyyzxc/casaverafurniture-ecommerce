<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\ActivityLog;
use App\Models\StockLog;
use App\Events\ProductCreated;
use App\Events\ProductUpdated;
use App\Events\ProductDeleted;
use App\Events\StockChanged;
use App\Events\HomepageUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category:id,name', 'primaryImage']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($stockStatus = $request->input('stock_status')) {
            $query->where('stock_status', $stockStatus);
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        if ($request->boolean('low_stock')) {
            $query->where('track_inventory', true)
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'images', 'tags', 'collections']);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'sale_starts_at' => ['nullable', 'date'],
            'sale_ends_at' => ['nullable', 'date', 'after:sale_starts_at'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'track_inventory' => ['boolean'],
            'allow_backorder' => ['boolean'],
            'status' => ['required', Rule::in(['active', 'hidden', 'draft', 'archived'])],
            'is_featured' => ['boolean'],
            'is_new' => ['boolean'],
            'is_bestseller' => ['boolean'],
            'dimensions' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'material' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:100'],
            'attributes' => ['nullable', 'array'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array'],
            'images.*.image_path' => ['required', 'string'],
            'images.*.is_primary' => ['boolean'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Product::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter++;
        }

        $validated['stock_status'] = $validated['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock';

        if ($validated['status'] === 'active') {
            $validated['published_at'] = now();
        }

        $product = Product::create($validated);

        if (!empty($validated['images'])) {
            foreach ($validated['images'] as $index => $imageData) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imageData['image_path'],
                    'is_primary' => $imageData['is_primary'] ?? ($index === 0),
                    'display_order' => $index,
                ]);
            }
        }

        if (!empty($validated['tag_ids'])) {
            $product->tags()->sync($validated['tag_ids']);
        }

        if ($product->category_id) {
            Category::where('id', $product->category_id)->increment('product_count');
        }

        ActivityLog::log('create', 'products', "Created product: {$product->name}", $product);

        $product->load(['category', 'images', 'tags']);

        if ($product->status === 'active') {
            event(new ProductCreated($product));

            if ($product->is_featured) {
                event(new HomepageUpdated('featured_products', [
                    'product_id' => $product->id,
                    'is_featured' => true,
                ]));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => $product,
        ], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'sku' => ['sometimes', 'string', 'max:100', Rule::unique('products')->ignore($product->id)],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'sale_starts_at' => ['nullable', 'date'],
            'sale_ends_at' => ['nullable', 'date', 'after:sale_starts_at'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'track_inventory' => ['boolean'],
            'allow_backorder' => ['boolean'],
            'status' => ['sometimes', Rule::in(['active', 'hidden', 'draft', 'archived'])],
            'is_featured' => ['boolean'],
            'is_new' => ['boolean'],
            'is_bestseller' => ['boolean'],
            'dimensions' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'material' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:100'],
            'attributes' => ['nullable', 'array'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array'],
            'images.*.id' => ['nullable', 'exists:product_images,id'],
            'images.*.image_path' => ['required', 'string'],
            'images.*.is_primary' => ['boolean'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
        ]);

        $oldCategoryId = $product->category_id;

        if (isset($validated['name']) && $validated['name'] !== $product->name) {
            $validated['slug'] = Str::slug($validated['name']);
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Product::where('slug', $validated['slug'])->where('id', '!=', $product->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }
        }

        if (isset($validated['stock_quantity'])) {
            $validated['stock_status'] = $validated['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock';
        }

        if (isset($validated['status']) && $validated['status'] === 'active' && !$product->published_at) {
            $validated['published_at'] = now();
        }

        $oldValues = $product->toArray();
        $oldStockQuantity = $product->stock_quantity;

        $product->update($validated);
        $product->refresh();

        if ($request->has('images')) {
            $images = $request->input('images');

            if (is_array($images)) {
                $newImageIds = collect($images)->pluck('id')->filter()->toArray();
                $product->images()->whereNotIn('id', $newImageIds)->delete();

                foreach ($images as $index => $imageData) {
                    if (!empty($imageData['id'])) {
                        $updateData = [
                            'is_primary' => $imageData['is_primary'] ?? false,
                            'display_order' => $index,
                        ];

                        if (!empty($imageData['image_path'])) {
                            $updateData['image_path'] = $imageData['image_path'];
                        }

                        $product->images()->where('id', $imageData['id'])->update($updateData);
                    } elseif (!empty($imageData['image_path'])) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $imageData['image_path'],
                            'is_primary' => $imageData['is_primary'] ?? false,
                            'display_order' => $index,
                        ]);
                    }
                }
            }
        }

        if (isset($validated['tag_ids'])) {
            $product->tags()->sync($validated['tag_ids']);
        }

        if (isset($validated['category_id']) && $oldCategoryId !== $validated['category_id']) {
            if ($oldCategoryId) {
                Category::where('id', $oldCategoryId)->decrement('product_count');
            }
            if ($validated['category_id']) {
                Category::where('id', $validated['category_id'])->increment('product_count');
            }
        }

        ActivityLog::log('update', 'products', "Updated product: {$product->name}", $product, $oldValues, $product->toArray());

        $product->load(['category', 'images', 'tags']);

        event(new ProductUpdated($product));

        if (isset($validated['stock_quantity']) && $oldStockQuantity !== $product->stock_quantity) {
            $stockType = 'update';
            if ($product->isLowStock() && !($oldStockQuantity <= $product->low_stock_threshold)) {
                $stockType = 'low_stock';
            } elseif ($product->isOutOfStock() && $oldStockQuantity > 0) {
                $stockType = 'out_of_stock';
            } elseif ($product->stock_quantity > $oldStockQuantity && $oldStockQuantity <= $product->low_stock_threshold) {
                $stockType = 'restocked';
            }

            event(new StockChanged($product, $oldStockQuantity, $product->stock_quantity, $stockType));
        }

        if (isset($validated['is_featured']) && $oldValues['is_featured'] !== $product->is_featured) {
            event(new HomepageUpdated('featured_products', [
                'product_id' => $product->id,
                'is_featured' => $product->is_featured,
            ]));
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => $product,
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        if ($product->category_id) {
            Category::where('id', $product->category_id)->decrement('product_count');
        }

        $productId = $product->id;
        $productSlug = $product->slug;

        ActivityLog::log('delete', 'products', "Deleted product: {$product->name}", $product);

        $product->delete();

        event(new ProductDeleted($productId, $productSlug));

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:products,id'],
            'action' => ['required', Rule::in(['activate', 'deactivate', 'feature', 'unfeature', 'delete'])],
        ]);

        $count = count($validated['ids']);

        switch ($validated['action']) {
            case 'activate':
                Product::whereIn('id', $validated['ids'])->update(['status' => 'active']);
                $message = "{$count} products activated.";
                break;
            case 'deactivate':
                Product::whereIn('id', $validated['ids'])->update(['status' => 'hidden']);
                $message = "{$count} products deactivated.";
                break;
            case 'feature':
                Product::whereIn('id', $validated['ids'])->update(['is_featured' => true]);
                $message = "{$count} products marked as featured.";
                break;
            case 'unfeature':
                Product::whereIn('id', $validated['ids'])->update(['is_featured' => false]);
                $message = "{$count} products unmarked as featured.";
                break;
            case 'delete':
                Product::whereIn('id', $validated['ids'])->delete();
                $message = "{$count} products deleted.";
                break;
        }

        ActivityLog::log('bulk_update', 'products', $message);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function updateStock(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'type' => ['required', Rule::in(['set', 'add', 'subtract'])],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $oldQuantity = $product->stock_quantity;

        switch ($validated['type']) {
            case 'set':
                $newQuantity = $validated['quantity'];
                break;
            case 'add':
                $newQuantity = $oldQuantity + $validated['quantity'];
                break;
            case 'subtract':
                $newQuantity = max(0, $oldQuantity - $validated['quantity']);
                break;
        }

        DB::transaction(function () use ($product, $newQuantity, $oldQuantity, $validated) {
            $product->update([
                'stock_quantity' => $newQuantity,
                'stock_status' => $newQuantity > 0 ? 'in_stock' : 'out_of_stock',
            ]);

            $admin = Auth::user();
            $adminName = $admin ? ($admin->first_name . ' ' . $admin->last_name) : 'System';

            StockLog::create([
                'product_id' => $product->id,
                'quantity_change' => $newQuantity - $oldQuantity,
                'quantity_before' => $oldQuantity,
                'quantity_after' => $newQuantity,
                'type' => 'adjustment',
                'reference_type' => 'adjustment',
                'reference_id' => null,
                'reference_number' => null,
                'notes' => $validated['reason'] ?? 'Manual stock adjustment',
                'admin_id' => $admin ? $admin->id : null,
                'admin_name' => $adminName,
            ]);
        });

        $stockType = 'update';
        if ($product->isLowStock() && !($oldQuantity <= $product->low_stock_threshold)) {
            $stockType = 'low_stock';
        } elseif ($product->isOutOfStock() && $oldQuantity > 0) {
            $stockType = 'out_of_stock';
        } elseif ($newQuantity > $oldQuantity && $oldQuantity <= $product->low_stock_threshold) {
            $stockType = 'restocked';
        }

        ActivityLog::log(
            'stock_update',
            'products',
            "Updated stock for product: {$product->name} from {$oldQuantity} to {$newQuantity}",
            $product,
            ['stock_quantity' => $oldQuantity],
            ['stock_quantity' => $newQuantity],
            ['type' => $validated['type'], 'reason' => $validated['reason'] ?? null]
        );

        event(new StockChanged($product, $oldQuantity, $newQuantity, $stockType));

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully.',
            'data' => [
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
            ],
        ]);
    }

    public function getStockHistory(Product $product, Request $request): JsonResponse
    {
        try {
            $perPage = min($request->input('per_page', 50), 100);

            $logs = StockLog::where('product_id', $product->id)
                ->with(['admin:id,first_name,last_name'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $logs->getCollection()->transform(function ($log) {
                return [
                    'id' => $log->id,
                    'product_id' => $log->product_id,
                    'quantity_change' => $log->quantity_change,
                    'quantity_before' => $log->quantity_before,
                    'quantity_after' => $log->quantity_after,
                    'type' => $log->type,
                    'reference_type' => $log->reference_type,
                    'reference_id' => $log->reference_id,
                    'reference_number' => $log->reference_number,
                    'notes' => $log->notes,
                    'reason' => $log->notes,
                    'admin_id' => $log->admin_id,
                    'admin_name' => $log->admin_name ?? ($log->admin ? $log->admin->first_name . ' ' . $log->admin->last_name : 'System'),
                    'unit_cost' => $log->unit_cost,
                    'total_cost' => $log->total_cost,
                    'created_at' => $log->created_at->toISOString(),
                    'updated_at' => $log->updated_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get stock history', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load stock history.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
