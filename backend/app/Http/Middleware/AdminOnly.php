<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Admin;

class AdminOnly
{
    /**
     * Handle an incoming request.
     * 
     * Ensures that only Admin models can access admin routes.
     * Prevents User models from accessing admin endpoints.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        // Check if user is an Admin model (not a User model)
        if (!$user instanceof Admin) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.',
            ], 403);
        }

        // Check if admin is active
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your admin account has been deactivated.',
            ], 403);
        }

        return $next($request);
    }
}
