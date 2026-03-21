<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\PaymentMethod;
use App\Models\ShippingZone;
use App\Models\Courier;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $group = $request->input('group');

        $query = SiteSetting::query();

        if ($group) {
            $query->where('group', $group);
        }

        $settings = $query->get()->groupBy('group')->map(function ($items) {
            return $items->mapWithKeys(fn($item) => [$item->key => $item->typed_value]);
        });

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string'],
            'settings.*.value' => ['nullable'],
            'settings.*.group' => ['nullable', 'string'],
        ]);

        foreach ($validated['settings'] as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => is_array($setting['value']) ? json_encode($setting['value']) : $setting['value'],
                    'group' => $setting['group'] ?? 'general',
                ]
            );
        }

        Cache::flush();

        ActivityLog::log('update', 'settings', 'Updated site settings');

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully.',
        ]);
    }

    public function paymentMethods(): JsonResponse
    {
        $methods = PaymentMethod::orderBy('display_order')->get();

        return response()->json([
            'success' => true,
            'data' => $methods,
        ]);
    }

    public function updatePaymentMethod(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'payment_instructions' => ['nullable', 'string'],
            'account_details' => ['nullable', 'array'],
            'fee_fixed' => ['nullable', 'numeric', 'min:0'],
            'fee_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0'],
            'requires_verification' => ['boolean'],
            'requires_proof_of_payment' => ['boolean'],
            'is_active' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $paymentMethod->update($validated);

        ActivityLog::log('update', 'payment_methods', "Updated payment method: {$paymentMethod->name}", $paymentMethod);

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully.',
            'data' => $paymentMethod,
        ]);
    }

    public function shippingZones(): JsonResponse
    {
        $zones = ShippingZone::with('rates')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $zones,
        ]);
    }

    public function updateShippingZone(Request $request, ShippingZone $shippingZone): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'regions' => ['nullable', 'array'],
            'base_rate' => ['nullable', 'numeric', 'min:0'],
            'free_shipping_threshold' => ['nullable', 'numeric', 'min:0'],
            'min_delivery_days' => ['nullable', 'integer', 'min:1'],
            'max_delivery_days' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        $shippingZone->update($validated);

        ActivityLog::log('update', 'shipping', "Updated shipping zone: {$shippingZone->name}", $shippingZone);

        return response()->json([
            'success' => true,
            'message' => 'Shipping zone updated successfully.',
            'data' => $shippingZone,
        ]);
    }

    public function couriers(): JsonResponse
    {
        $couriers = Courier::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $couriers,
        ]);
    }

    public function updateCourier(Request $request, Courier $courier): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'tracking_url' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $courier->update($validated);

        ActivityLog::log('update', 'couriers', "Updated courier: {$courier->name}", $courier);

        return response()->json([
            'success' => true,
            'message' => 'Courier updated successfully.',
            'data' => $courier,
        ]);
    }
}
