<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * List all users
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Add aggregates for accurate reporting
        // We calculate these on the fly to ensure they are always up to date
        $query->withCount('orders');
        $query->withSum(['orders' => function ($q) {
            $q->where('payment_status', 'paid');
        }], 'total');

        // Pagination
        $perPage = $request->input('per_page', 15);
        $users = $query->paginate($perPage);

        // Override the cached columns with the calculated values
        $users->getCollection()->transform(function ($user) {
            $user->order_count = $user->orders_count;
            $user->total_spent = $user->orders_sum_total ?? 0;
            return $user;
        });

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Get single user
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['addresses', 'orders' => function ($q) {
            $q->latest()->limit(10);
        }]);

        // Also calculate for single user view
        $user->loadCount('orders');
        $user->loadSum(['orders' => function ($q) {
            $q->where('payment_status', 'paid');
        }], 'total');

        $user->order_count = $user->orders_count;
        $user->total_spent = $user->orders_sum_total ?? 0;

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'banned', 'pending_verification'])],
            'ban_reason' => ['nullable', 'required_if:status,banned', 'string', 'max:500'],
        ]);

        if (isset($validated['status']) && $validated['status'] === 'banned') {
            $validated['banned_at'] = now();
        } elseif (isset($validated['status']) && $validated['status'] !== 'banned') {
            $validated['banned_at'] = null;
            $validated['ban_reason'] = null;
        }

        $user->update($validated);

        ActivityLog::log('update', 'users', "Updated user: {$user->full_name}", $user);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data' => $user,
        ]);
    }

    /**
     * Ban user
     */
    public function ban(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $user->update([
            'status' => 'banned',
            'ban_reason' => $validated['reason'],
            'banned_at' => now(),
        ]);

        // Revoke all tokens
        $user->tokens()->delete();

        ActivityLog::log('ban', 'users', "Banned user: {$user->full_name}", $user);

        return response()->json([
            'success' => true,
            'message' => 'User banned successfully.',
        ]);
    }

    /**
     * Unban user
     */
    public function unban(User $user): JsonResponse
    {
        $user->update([
            'status' => 'active',
            'ban_reason' => null,
            'banned_at' => null,
        ]);

        ActivityLog::log('unban', 'users', "Unbanned user: {$user->full_name}", $user);

        return response()->json([
            'success' => true,
            'message' => 'User unbanned successfully.',
        ]);
    }

    /**
     * Delete user (Permanent Delete)
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userName = $user->full_name;
            $userEmail = $user->email;

            // Delete related data to ensure clean removal
            $user->tokens()->delete();
            $user->addresses()->delete();
            $user->cart()->delete();
            $user->wishlists()->delete();

            // Note: We are not deleting orders here to preserve business records.
            // If the database has strict foreign key constraints on orders,
            // this might fail unless ON DELETE SET NULL or CASCADE is configured.
            // Assuming we want to keep orders but remove the user link if necessary:
            // $user->orders()->update(['user_id' => null]);

            // Permanently delete the user
            $user->forceDelete();

            DB::commit();

            // Log activity
            ActivityLog::log(
                'delete',
                'users',
                "Permanently deleted user: {$userName} ({$userEmail})",
                null // User is gone, so no subject
            );

            return response()->json([
                'success' => true,
                'message' => 'User permanently deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to delete user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get user orders
     */
    public function orders(User $user, Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $orders = $user->orders()
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }
}
