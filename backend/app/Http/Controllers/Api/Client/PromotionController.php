<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PromotionController extends Controller
{
    /**
     * Get visible promotions for clients
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $now = Carbon::now();
            
            $query = Promotion::query()
                ->where('is_visible', true)
                ->where('is_active', true)
                ->where('starts_at', '<=', $now)
                ->where(function ($q) use ($now) {
                    $q->whereNull('ends_at')
                      ->orWhere('ends_at', '>=', $now);
                });

            // Get only active and visible promotions
            $promotions = $query->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // Transform data for frontend
            $transformed = $promotions->map(function ($promotion) {
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
                    'minOrderAmount' => $promotion->min_order_amount ? (float) $promotion->min_order_amount : null,
                    'isVisible' => $promotion->is_visible,
                    'createdAt' => $promotion->created_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformed,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get visible promotions', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load promotions.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
