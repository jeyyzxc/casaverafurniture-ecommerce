<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShippingController extends Controller
{
    /**
     * List all shipping zones
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ShippingZone::with('rates')->orderBy('display_order')->orderBy('name');

            // Filter by active status if requested
            if ($request->has('active_only')) {
                $query->where('is_active', true);
            }

            $zones = $query->get();

            return response()->json([
                'success' => true,
                'data' => $zones,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch shipping zones', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch shipping zones.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get single shipping zone
     */
    public function show(ShippingZone $shippingZone): JsonResponse
    {
        $shippingZone->load('rates', 'couriers');

        return response()->json([
            'success' => true,
            'data' => $shippingZone,
        ]);
    }

    /**
     * Create new shipping zone
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(['local', 'national', 'international'])],
            'description' => ['nullable', 'string'],
            'regions' => ['nullable', 'array'],
            'postal_codes' => ['nullable', 'array'],
            'base_rate' => ['required', 'numeric', 'min:0'],
            'free_shipping_threshold' => ['nullable', 'numeric', 'min:0'],
            'min_delivery_days' => ['required', 'integer', 'min:1'],
            'max_delivery_days' => ['required', 'integer', 'min:1', 'gte:min_delivery_days'],
            'is_active' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $zone = ShippingZone::create($validated);

            ActivityLog::log(
                'create',
                'shipping_zones',
                "Created shipping zone: {$zone->name}",
                $zone
            );

            $zone->load('rates');

            return response()->json([
                'success' => true,
                'message' => 'Shipping zone created successfully.',
                'data' => $zone,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Failed to create shipping zone', [
                'error' => $e->getMessage(),
                'data' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create shipping zone.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update shipping zone
     */
    public function update(Request $request, ShippingZone $shippingZone): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'type' => ['sometimes', Rule::in(['local', 'national', 'international'])],
            'description' => ['nullable', 'string'],
            'regions' => ['nullable', 'array'],
            'postal_codes' => ['nullable', 'array'],
            'base_rate' => ['sometimes', 'numeric', 'min:0'],
            'free_shipping_threshold' => ['nullable', 'numeric', 'min:0'],
            'min_delivery_days' => ['sometimes', 'integer', 'min:1'],
            'max_delivery_days' => ['sometimes', 'integer', 'min:1'],
            'is_active' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Validate max_delivery_days >= min_delivery_days if both are provided
        if (isset($validated['max_delivery_days']) && isset($validated['min_delivery_days'])) {
            if ($validated['max_delivery_days'] < $validated['min_delivery_days']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum delivery days must be greater than or equal to minimum delivery days.',
                ], 422);
            }
        } elseif (isset($validated['max_delivery_days'])) {
            $minDays = $shippingZone->min_delivery_days;
            if ($validated['max_delivery_days'] < $minDays) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum delivery days must be greater than or equal to minimum delivery days.',
                ], 422);
            }
        } elseif (isset($validated['min_delivery_days'])) {
            $maxDays = $shippingZone->max_delivery_days;
            if ($maxDays < $validated['min_delivery_days']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum delivery days must be greater than or equal to minimum delivery days.',
                ], 422);
            }
        }

        try {
            $shippingZone->update($validated);

            ActivityLog::log(
                'update',
                'shipping_zones',
                "Updated shipping zone: {$shippingZone->name}",
                $shippingZone
            );

            $shippingZone->load('rates');

            return response()->json([
                'success' => true,
                'message' => 'Shipping zone updated successfully.',
                'data' => $shippingZone,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update shipping zone', [
                'zone_id' => $shippingZone->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update shipping zone.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete shipping zone (soft delete)
     */
    public function destroy(ShippingZone $shippingZone): JsonResponse
    {
        try {
            $zoneName = $shippingZone->name;

            // Check if zone is used in any orders (optional check - soft delete allows this)
            // We'll allow deletion but keep the record for historical purposes

            $shippingZone->delete();

            ActivityLog::log(
                'delete',
                'shipping_zones',
                "Deleted shipping zone: {$zoneName}",
                $shippingZone
            );

            return response()->json([
                'success' => true,
                'message' => 'Shipping zone deleted successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to delete shipping zone', [
                'zone_id' => $shippingZone->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete shipping zone.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
