<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\Admin;
use App\Models\RefreshToken;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    /**
     * Admin Login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        // Find admin by email
        $admin = Admin::with('role.permissions')->where('email', $credentials['email'])->first();

        // Check if admin exists and password is correct
        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            // Log failed attempt
            DB::table('admin_login_logs')->insert([
                'admin_id' => $admin?->id,
                'email' => $credentials['email'],
                'status' => 'failed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // Check if admin is active
        if ($admin->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact the administrator.',
            ], 403);
        }

        // Revoke existing tokens
        $admin->tokens()->delete();
        RefreshToken::revokeAllFor($admin);

        // Generate access and refresh tokens
        // Admin tokens expire in 15 days (stricter than client)
        $tokens = $admin->generateTokens('admin-token', ['admin'], 15);

        // Update login info
        $admin->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Log successful login
        DB::table('admin_login_logs')->insert([
            'admin_id' => $admin->id,
            'email' => $credentials['email'],
            'status' => 'success',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        // Log activity
        ActivityLog::log('login', 'auth', "Admin {$admin->full_name} logged in", $admin);

        // Create HTTP-only cookie for refresh token
        $refreshTokenCookie = cookie(
            'admin_refresh_token',
            $tokens['refresh_token'],
            15 * 24 * 60, // 15 days in minutes (stricter than client)
            '/',
            null,
            config('app.env') === 'production', // Secure in production
            true, // HttpOnly
            false, // Raw
            'Lax' // SameSite
        );

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'admin' => [
                    'id' => $admin->id,
                    'first_name' => $admin->first_name,
                    'last_name' => $admin->last_name,
                    'full_name' => $admin->full_name,
                    'email' => $admin->email,
                    'avatar' => $admin->avatar,
                    'role' => $admin->role ? [
                        'id' => $admin->role->id,
                        'name' => $admin->role->name,
                        'slug' => $admin->role->slug,
                    ] : null,
                    'permissions' => $admin->role?->permissions->pluck('slug')->toArray() ?? [],
                ],
                'access_token' => $tokens['access_token'],
            ],
        ])->cookie($refreshTokenCookie);
    }

    /**
     * Get Current Admin
     */
    public function me(Request $request): JsonResponse
    {
        $admin = $request->user();
        $admin->load('role.permissions');

        return response()->json([
            'success' => true,
            'data' => [
                'admin' => [
                    'id' => $admin->id,
                    'first_name' => $admin->first_name,
                    'last_name' => $admin->last_name,
                    'full_name' => $admin->full_name,
                    'email' => $admin->email,
                    'phone' => $admin->phone,
                    'avatar' => $admin->avatar,
                    'role' => $admin->role ? [
                        'id' => $admin->role->id,
                        'name' => $admin->role->name,
                        'slug' => $admin->role->slug,
                    ] : null,
                    'permissions' => $admin->role?->permissions->pluck('slug')->toArray() ?? [],
                    'last_login_at' => $admin->last_login_at,
                ],
            ],
        ]);
    }

    /**
     * Admin Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $admin = $request->user();

        // Revoke all tokens (access and refresh)
        $admin->revokeAllTokens();

        // Clear refresh token cookie
        $cookie = cookie()->forget('admin_refresh_token');

        // Log activity
        ActivityLog::log('logout', 'auth', "Admin {$admin->full_name} logged out", $admin);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ])->cookie($cookie);
    }

    /**
     * Refresh Access Token
     */
    public function refresh(Request $request): JsonResponse
    {
        // Get refresh token from cookie
        $refreshToken = $request->cookie('admin_refresh_token');

        if (!$refreshToken) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token not found.',
            ], 401);
        }

        // Find and validate refresh token
        $tokenRecord = RefreshToken::findToken($refreshToken);

        if (!$tokenRecord) {
            // Clear invalid cookie
            $cookie = cookie()->forget('admin_refresh_token');
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired refresh token.',
            ], 401)->cookie($cookie);
        }

        // Get the admin
        $admin = $tokenRecord->tokenable;

        // Ensure it's an Admin model, not a User
        if (!$admin instanceof Admin || $admin->status !== 'active') {
            $tokenRecord->revoke();
            $cookie = cookie()->forget('admin_refresh_token');
            return response()->json([
                'success' => false,
                'message' => 'Admin account is not active.',
            ], 403)->cookie($cookie);
        }

        // Generate new tokens (shorter expiry for testing refresh flow: 15 mins for access, 15 days for refresh)
        $tokens = $admin->generateTokens('admin-token', ['admin'], 15);

        // Revoke old refresh token (one-time use)
        // Note: We do this AFTER generating new tokens to slightly reduce the race window,
        // though generateTokens itself creates a record.
        $tokenRecord->revoke();

        // Create new HTTP-only cookie for refresh token
        $refreshTokenCookie = cookie(
            'admin_refresh_token',
            $tokens['refresh_token'],
            15 * 24 * 60, // 15 days in minutes
            '/',
            null,
            config('app.env') === 'production', // Secure in production
            true, // HttpOnly
            false, // Raw
            'Lax' // SameSite
        );

        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $tokens['access_token'],
            ],
        ])->cookie($refreshTokenCookie);
    }

    /**
     * Update Profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $admin = $request->user();

        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'string', 'max:255'],
        ]);

        $oldValues = [];
        foreach ($validated as $key => $value) {
            if (isset($admin->getOriginal()[$key])) {
                $oldValues[$key] = $admin->getOriginal()[$key];
            }
        }

        $admin->update($validated);

        // Log activity
        ActivityLog::log(
            'update',
            'auth',
            "Updated profile: {$admin->full_name}",
            $admin,
            $oldValues,
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'admin' => [
                    'id' => $admin->id,
                    'first_name' => $admin->first_name,
                    'last_name' => $admin->last_name,
                    'full_name' => $admin->full_name,
                    'email' => $admin->email,
                    'phone' => $admin->phone,
                    'avatar' => $admin->avatar,
                ],
            ],
        ]);
    }

    /**
     * Change Password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin = $request->user();

        if (!Hash::check($validated['current_password'], $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $admin->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Revoke all tokens except current
        $admin->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        // Log activity
        ActivityLog::log('password_changed', 'auth', "Admin {$admin->full_name} changed password", $admin);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }
}
