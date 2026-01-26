<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * List all admins
     */
    public function index(Request $request): JsonResponse
    {
        $query = Admin::with(['role:id,name,slug'])
            ->orderBy('created_at', 'desc');

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Pagination
        $perPage = min($request->input('per_page', 15), 50);
        $admins = $query->paginate($perPage);

        // Transform admins for frontend
        $admins->getCollection()->transform(function ($admin) {
            return [
                'id' => $admin->id,
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'full_name' => $admin->full_name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'avatar' => $admin->avatar,
                'role_id' => $admin->role_id,
                'role' => $admin->role ? [
                    'id' => $admin->role->id,
                    'name' => $admin->role->name,
                    'slug' => $admin->role->slug,
                ] : null,
                'status' => $admin->status,
                'last_login_at' => $admin->last_login_at?->toISOString(),
                'last_login_ip' => $admin->last_login_ip,
                'created_at' => $admin->created_at->toISOString(),
                'updated_at' => $admin->updated_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $admins,
        ]);
    }

    /**
     * Get single admin
     */
    public function show(Admin $admin): JsonResponse
    {
        $admin->load(['role:id,name,slug,description']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $admin->id,
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'full_name' => $admin->full_name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'avatar' => $admin->avatar,
                'role_id' => $admin->role_id,
                'role' => $admin->role ? [
                    'id' => $admin->role->id,
                    'name' => $admin->role->name,
                    'slug' => $admin->role->slug,
                    'description' => $admin->role->description,
                ] : null,
                'status' => $admin->status,
                'last_login_at' => $admin->last_login_at?->toISOString(),
                'last_login_ip' => $admin->last_login_ip,
                'created_at' => $admin->created_at->toISOString(),
                'updated_at' => $admin->updated_at->toISOString(),
            ],
        ]);
    }

    /**
     * Create new admin (Super Admin only)
     */
    public function store(Request $request): JsonResponse
    {
        // Check if current user is super-admin
        $currentAdmin = $request->user();
        if (!$currentAdmin || $currentAdmin->role->slug !== 'super-admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only Super Admin can create admin accounts.',
            ], 403);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        // Prevent creating another super-admin (optional security measure)
        $role = Role::findOrFail($validated['role_id']);
        if ($role->slug === 'super-admin' && $currentAdmin->id !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot create Super Admin account.',
            ], 403);
        }

        $admin = Admin::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role_id' => $validated['role_id'],
            'status' => $validated['status'] ?? 'active',
        ]);

        $admin->load(['role:id,name,slug']);

        // Log activity
        ActivityLog::log(
            'create',
            'admins',
            "Created admin account: {$admin->full_name} ({$admin->email}) with role: {$admin->role->name}",
            $admin
        );

        return response()->json([
            'success' => true,
            'message' => 'Admin created successfully.',
            'data' => [
                'id' => $admin->id,
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'full_name' => $admin->full_name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'role_id' => $admin->role_id,
                'role' => [
                    'id' => $admin->role->id,
                    'name' => $admin->role->name,
                    'slug' => $admin->role->slug,
                ],
                'status' => $admin->status,
            ],
        ], 201);
    }

    /**
     * Update admin (Super Admin only, or self for profile updates)
     */
    public function update(Request $request, Admin $admin): JsonResponse
    {
        $currentAdmin = $request->user();

        // Check permissions: Super Admin can update anyone, others can only update themselves
        $isSuperAdmin = $currentAdmin->role->slug === 'super-admin';
        $isSelf = $currentAdmin->id === $admin->id;

        if (!$isSuperAdmin && !$isSelf) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this admin account.',
            ], 403);
        }

        // If not super-admin, restrict what can be updated
        $allowedFields = ['first_name', 'last_name', 'phone', 'avatar'];
        if ($isSuperAdmin) {
            $allowedFields = array_merge($allowedFields, ['email', 'role_id', 'status']);
        }

        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('admins')->ignore($admin->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'string', 'max:255'],
            'role_id' => $isSuperAdmin ? ['sometimes', 'exists:roles,id'] : ['prohibited'],
            'status' => $isSuperAdmin ? ['sometimes', Rule::in(['active', 'inactive'])] : ['prohibited'],
        ]);

        // Prevent changing super-admin role (except by first super-admin)
        if (isset($validated['role_id']) && $admin->role->slug === 'super-admin') {
            if ($currentAdmin->id !== 1 || $validated['role_id'] !== $admin->role_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot change Super Admin role.',
                ], 403);
            }
        }

        // Prevent making another admin super-admin (except first super-admin)
        if (isset($validated['role_id'])) {
            $newRole = Role::findOrFail($validated['role_id']);
            if ($newRole->slug === 'super-admin' && $currentAdmin->id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot assign Super Admin role.',
                ], 403);
            }
        }

        $oldValues = $admin->only(['first_name', 'last_name', 'email', 'phone', 'role_id', 'status']);
        $admin->update($validated);
        $admin->load(['role:id,name,slug']);

        // Log activity
        $changes = [];
        foreach ($validated as $key => $value) {
            if (isset($oldValues[$key]) && $oldValues[$key] != $value) {
                $changes[$key] = ['old' => $oldValues[$key], 'new' => $value];
            }
        }

        ActivityLog::log(
            'update',
            'admins',
            "Updated admin account: {$admin->full_name} ({$admin->email})",
            $admin,
            $oldValues,
            $admin->toArray()
        );

        return response()->json([
            'success' => true,
            'message' => 'Admin updated successfully.',
            'data' => [
                'id' => $admin->id,
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'full_name' => $admin->full_name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'avatar' => $admin->avatar,
                'role_id' => $admin->role_id,
                'role' => [
                    'id' => $admin->role->id,
                    'name' => $admin->role->name,
                    'slug' => $admin->role->slug,
                ],
                'status' => $admin->status,
            ],
        ]);
    }

    /**
     * Delete admin (Super Admin only)
     */
    public function destroy(Admin $admin): JsonResponse
    {
        $currentAdmin = request()->user();

        // Only super-admin can delete
        if (!$currentAdmin || $currentAdmin->role->slug !== 'super-admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only Super Admin can delete admin accounts.',
            ], 403);
        }

        // Prevent deleting self
        if ($currentAdmin->id === $admin->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        // Prevent deleting super-admin (except by first super-admin)
        if ($admin->role->slug === 'super-admin' && $currentAdmin->id !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete Super Admin account.',
            ], 403);
        }

        $adminName = $admin->full_name;
        $adminEmail = $admin->email;

        // Log activity before deletion
        ActivityLog::log(
            'delete',
            'admins',
            "Deleted admin account: {$adminName} ({$adminEmail})",
            $admin
        );

        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin deleted successfully.',
        ]);
    }

    /**
     * Get available roles (for dropdown)
     */
    public function roles(): JsonResponse
    {
        $roles = Role::select('id', 'name', 'slug', 'description')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }
}
