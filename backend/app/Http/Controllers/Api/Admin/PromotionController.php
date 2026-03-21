<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Promotion::query();

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($type = $request->input('discount_type')) {
                $query->where('discount_type', $type);
            }

            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->input('per_page', 15);
            $promotions = $query->paginate($perPage);

            $promotions->getCollection()->transform(function ($promotion) {
                return $this->transformPromotion($promotion);
            });

            return response()->json([
                'success' => true,
                'data' => $promotions,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get promotions', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load promotions.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show(Promotion $promotion): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->transformPromotion($promotion),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load promotion.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:200'],
                'code' => ['required', 'string', 'max:50', 'unique:promotions,code'],
                'description' => ['nullable', 'string'],
                'discount_type' => ['required', 'in:percentage,fixed,free_shipping,buy_x_get_y'],
                'discount_value' => ['required', 'numeric', 'min:0'],
                'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
                'buy_quantity' => ['nullable', 'integer', 'min:1'],
                'get_quantity' => ['nullable', 'integer', 'min:1'],
                'get_discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'applies_to' => ['required', 'in:all,specific_products,specific_categories,specific_collections'],
                'applicable_product_ids' => ['nullable', 'array'],
                'applicable_category_ids' => ['nullable', 'array'],
                'applicable_collection_ids' => ['nullable', 'array'],
                'min_order_amount' => ['nullable', 'numeric', 'min:0'],
                'max_order_amount' => ['nullable', 'numeric', 'min:0'],
                'min_quantity' => ['nullable', 'integer', 'min:1'],
                'usage_limit' => ['nullable', 'integer', 'min:1'],
                'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
                'starts_at' => ['required', 'date'],
                'ends_at' => ['nullable', 'date', 'after:starts_at'],
                'first_order_only' => ['nullable', 'boolean'],
                'allowed_user_ids' => ['nullable', 'array'],
                'allowed_user_emails' => ['nullable', 'array'],
                'is_active' => ['nullable', 'boolean'],
                'is_visible' => ['nullable', 'boolean'],
                'auto_apply' => ['nullable', 'boolean'],
                'priority' => ['nullable', 'integer', 'min:0'],
                'combinable_with_other_promotions' => ['nullable', 'boolean'],
            ]);

            $validated['code'] = strtoupper($validated['code']);

            $validated['created_by_admin_id'] = Auth::id();

            $promotion = Promotion::create($validated);

            ActivityLog::log(
                'create',
                'promotions',
                "Created promotion: {$promotion->name} ({$promotion->code})",
                $promotion
            );

            return response()->json([
                'success' => true,
                'message' => 'Promotion created successfully.',
                'data' => $this->transformPromotion($promotion),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to create promotion', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create promotion.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function update(Request $request, Promotion $promotion): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['sometimes', 'required', 'string', 'max:200'],
                'code' => ['sometimes', 'required', 'string', 'max:50', 'unique:promotions,code,' . $promotion->id],
                'description' => ['nullable', 'string'],
                'discount_type' => ['sometimes', 'required', 'in:percentage,fixed,free_shipping,buy_x_get_y'],
                'discount_value' => ['sometimes', 'required', 'numeric', 'min:0'],
                'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
                'buy_quantity' => ['nullable', 'integer', 'min:1'],
                'get_quantity' => ['nullable', 'integer', 'min:1'],
                'get_discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'applies_to' => ['sometimes', 'required', 'in:all,specific_products,specific_categories,specific_collections'],
                'applicable_product_ids' => ['nullable', 'array'],
                'applicable_category_ids' => ['nullable', 'array'],
                'applicable_collection_ids' => ['nullable', 'array'],
                'min_order_amount' => ['nullable', 'numeric', 'min:0'],
                'max_order_amount' => ['nullable', 'numeric', 'min:0'],
                'min_quantity' => ['nullable', 'integer', 'min:1'],
                'usage_limit' => ['nullable', 'integer', 'min:1'],
                'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
                'starts_at' => ['sometimes', 'required', 'date'],
                'ends_at' => ['nullable', 'date', 'after:starts_at'],
                'first_order_only' => ['nullable', 'boolean'],
                'allowed_user_ids' => ['nullable', 'array'],
                'allowed_user_emails' => ['nullable', 'array'],
                'is_active' => ['nullable', 'boolean'],
                'is_visible' => ['nullable', 'boolean'],
                'auto_apply' => ['nullable', 'boolean'],
                'priority' => ['nullable', 'integer', 'min:0'],
                'combinable_with_other_promotions' => ['nullable', 'boolean'],
            ]);

            if (isset($validated['code'])) {
                $validated['code'] = strtoupper($validated['code']);
            }

            $oldValues = $promotion->toArray();
            $promotion->update($validated);

            ActivityLog::log(
                'update',
                'promotions',
                "Updated promotion: {$promotion->name} ({$promotion->code})",
                $promotion,
                $oldValues,
                $promotion->toArray()
            );

            return response()->json([
                'success' => true,
                'message' => 'Promotion updated successfully.',
                'data' => $this->transformPromotion($promotion->fresh()),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to update promotion', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update promotion.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy(Promotion $promotion): JsonResponse
    {
        try {
            $promotionName = $promotion->name;
            $promotionCode = $promotion->code;
            $promotion->delete();

            ActivityLog::log(
                'delete',
                'promotions',
                "Deleted promotion: {$promotionName} ({$promotionCode})",
                $promotion
            );

            return response()->json([
                'success' => true,
                'message' => 'Promotion deleted successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to delete promotion', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete promotion.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function toggle(Promotion $promotion): JsonResponse
    {
        try {
            $promotion->is_active = !$promotion->is_active;
            $promotion->save();

            ActivityLog::log(
                'update',
                'promotions',
                ($promotion->is_active ? 'Activated' : 'Deactivated') . " promotion: {$promotion->name} ({$promotion->code})",
                $promotion
            );

            return response()->json([
                'success' => true,
                'message' => 'Promotion status updated successfully.',
                'data' => $this->transformPromotion($promotion->fresh()),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to toggle promotion', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update promotion status.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function transformPromotion(Promotion $promotion): array
    {
        $now = now();
        $isExpired = $promotion->ends_at && $now > $promotion->ends_at;

        return [
            'id' => $promotion->id,
            'name' => $promotion->name,
            'code' => $promotion->code,
            'description' => $promotion->description,
            'discountType' => $promotion->discount_type,
            'value' => (float) $promotion->discount_value,
            'maxDiscountAmount' => $promotion->max_discount_amount ? (float) $promotion->max_discount_amount : null,
            'startDate' => $promotion->starts_at ? $promotion->starts_at->toISOString() : null,
            'endDate' => $promotion->ends_at ? $promotion->ends_at->toISOString() : null,
            'usageLimit' => $promotion->usage_limit,
            'usageLimitPerUser' => $promotion->usage_limit_per_user,
            'usedCount' => $promotion->used_count,
            'isActive' => $promotion->is_active,
            'isExpired' => $isExpired,
            'minOrderAmount' => $promotion->min_order_amount ? (float) $promotion->min_order_amount : null,
            'appliesTo' => $promotion->applies_to,
            'applicableProductIds' => $promotion->applicable_product_ids ?? [],
            'applicableCategoryIds' => $promotion->applicable_category_ids ?? [],
            'applicableCollectionIds' => $promotion->applicable_collection_ids ?? [],
            'firstOrderOnly' => $promotion->first_order_only,
            'autoApply' => $promotion->auto_apply,
            'priority' => $promotion->priority,
            'combinableWithOtherPromotions' => $promotion->combinable_with_other_promotions,
            'createdAt' => $promotion->created_at->toISOString(),
            'updatedAt' => $promotion->updated_at->toISOString(),
        ];
    }
}
