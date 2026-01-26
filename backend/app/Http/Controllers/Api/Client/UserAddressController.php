<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserAddressController extends Controller
{
    /**
     * List user's addresses
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please log in.',
                ], 401);
            }
            
            $addresses = UserAddress::where('user_id', $user->id)
                ->orderBy('is_default_shipping', 'desc')
                ->orderBy('is_default_billing', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $addresses,
            ]);
        } catch (\Exception $e) {
            Log::error('UserAddressController index failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load addresses.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create new address
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please log in.',
                ], 401);
            }

            $validated = $request->validate([
                'label' => ['required', 'string', 'max:50'],
                'recipient_name' => ['required', 'string', 'max:100'],
                'phone' => ['required', 'string', 'max:20'],
                'address_line_1' => ['required', 'string', 'max:255'],
                'address_line_2' => ['nullable', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:100'],
                'province' => ['required', 'string', 'max:100'],
                'postal_code' => ['required', 'string', 'max:20'],
                'country' => ['nullable', 'string', 'max:100'],
                'is_default_shipping' => ['sometimes', 'boolean'],
                'is_default_billing' => ['sometimes', 'boolean'],
            ]);

            // Use transaction to ensure data consistency
            return DB::transaction(function () use ($user, $validated) {
                // If setting as default, unset other defaults
                if (!empty($validated['is_default_shipping'])) {
                    UserAddress::where('user_id', $user->id)
                        ->where('is_default_shipping', true)
                        ->update(['is_default_shipping' => false]);
                }

                if (!empty($validated['is_default_billing'])) {
                    UserAddress::where('user_id', $user->id)
                        ->where('is_default_billing', true)
                        ->update(['is_default_billing' => false]);
                }

                // Prepare data for creation with defaults
                $createData = array_merge($validated, [
                    'user_id' => $user->id,
                    'country' => $validated['country'] ?? 'Philippines',
                ]);
                
                // Ensure booleans are actual booleans (not strings)
                if (isset($createData['is_default_shipping'])) {
                    $createData['is_default_shipping'] = filter_var($createData['is_default_shipping'], FILTER_VALIDATE_BOOLEAN);
                }
                if (isset($createData['is_default_billing'])) {
                    $createData['is_default_billing'] = filter_var($createData['is_default_billing'], FILTER_VALIDATE_BOOLEAN);
                }
                
                $address = UserAddress::create($createData);

                return response()->json([
                    'success' => true,
                    'message' => 'Address added successfully.',
                    'data' => $address->fresh(),
                ], 201);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('UserAddressController store failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create address.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update address
     */
    public function update(Request $request, $userAddress): JsonResponse
    {
        try {
            // Handle route model binding - if it's an ID, fetch the model
            if (!($userAddress instanceof UserAddress)) {
                $userAddress = UserAddress::findOrFail($userAddress);
            }
            
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please log in.',
                ], 401);
            }

            // Ensure address belongs to user
            if ($userAddress->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 403);
            }

            $validated = $request->validate([
                'label' => ['sometimes', 'required', 'string', 'max:50'],
                'recipient_name' => ['sometimes', 'required', 'string', 'max:100'],
                'phone' => ['sometimes', 'required', 'string', 'max:20'],
                'address_line_1' => ['sometimes', 'required', 'string', 'max:255'],
                'address_line_2' => ['nullable', 'string', 'max:255'],
                'city' => ['sometimes', 'required', 'string', 'max:100'],
                'province' => ['sometimes', 'required', 'string', 'max:100'],
                'postal_code' => ['sometimes', 'required', 'string', 'max:20'],
                'country' => ['nullable', 'string', 'max:100'],
                'is_default_shipping' => ['sometimes', 'boolean'],
                'is_default_billing' => ['sometimes', 'boolean'],
            ]);

            // Use transaction to ensure data consistency
            return DB::transaction(function () use ($user, $userAddress, $validated) {
                // If setting as default, unset other defaults
                if (isset($validated['is_default_shipping']) && $validated['is_default_shipping']) {
                    UserAddress::where('user_id', $user->id)
                        ->where('id', '!=', $userAddress->id)
                        ->where('is_default_shipping', true)
                        ->update(['is_default_shipping' => false]);
                }

                if (isset($validated['is_default_billing']) && $validated['is_default_billing']) {
                    UserAddress::where('user_id', $user->id)
                        ->where('id', '!=', $userAddress->id)
                        ->where('is_default_billing', true)
                        ->update(['is_default_billing' => false]);
                }

                // Ensure booleans are actual booleans
                if (isset($validated['is_default_shipping'])) {
                    $validated['is_default_shipping'] = filter_var($validated['is_default_shipping'], FILTER_VALIDATE_BOOLEAN);
                }
                if (isset($validated['is_default_billing'])) {
                    $validated['is_default_billing'] = filter_var($validated['is_default_billing'], FILTER_VALIDATE_BOOLEAN);
                }

                $userAddress->update($validated);

                return response()->json([
                    'success' => true,
                    'message' => 'Address updated successfully.',
                    'data' => $userAddress->fresh(),
                ]);
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('UserAddressController update failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update address.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete address
     */
    public function destroy(Request $request, $userAddress): JsonResponse
    {
        try {
            // Handle route model binding - if it's an ID, fetch the model
            if (!($userAddress instanceof UserAddress)) {
                $userAddress = UserAddress::findOrFail($userAddress);
            }
            
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please log in.',
                ], 401);
            }

            // Ensure address belongs to user
            if ($userAddress->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 403);
            }

            $userAddress->delete();

            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('UserAddressController destroy failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete address.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Set default shipping address
     */
    public function setDefaultShipping(Request $request, $userAddress): JsonResponse
    {
        try {
            // Handle route model binding - if it's an ID, fetch the model
            if (!($userAddress instanceof UserAddress)) {
                $userAddress = UserAddress::findOrFail($userAddress);
            }
            
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please log in.',
                ], 401);
            }

            if ($userAddress->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 403);
            }

            // Use transaction to ensure data consistency
            DB::transaction(function () use ($user, $userAddress) {
                // Unset other defaults
                UserAddress::where('user_id', $user->id)
                    ->where('id', '!=', $userAddress->id)
                    ->update(['is_default_shipping' => false]);

                $userAddress->update(['is_default_shipping' => true]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Default shipping address updated.',
                'data' => $userAddress->fresh(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('UserAddressController setDefaultShipping failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update default shipping address.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Set default billing address
     */
    public function setDefaultBilling(Request $request, $userAddress): JsonResponse
    {
        try {
            // Handle route model binding - if it's an ID, fetch the model
            if (!($userAddress instanceof UserAddress)) {
                $userAddress = UserAddress::findOrFail($userAddress);
            }
            
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please log in.',
                ], 401);
            }

            if ($userAddress->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 403);
            }

            // Use transaction to ensure data consistency
            DB::transaction(function () use ($user, $userAddress) {
                // Unset other defaults
                UserAddress::where('user_id', $user->id)
                    ->where('id', '!=', $userAddress->id)
                    ->update(['is_default_billing' => false]);

                $userAddress->update(['is_default_billing' => true]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Default billing address updated.',
                'data' => $userAddress->fresh(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('UserAddressController setDefaultBilling failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update default billing address.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
