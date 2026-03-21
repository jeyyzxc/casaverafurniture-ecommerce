<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\LoginRequest;
use App\Http\Requests\Client\RegisterRequest;
use App\Models\User;
use App\Models\Order;
use App\Models\RefreshToken;
use App\Models\ActivityLog;
use App\Events\UserLogin;
use App\Events\UserRegistered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['regex:/^[^ ]/', 'regex:/^(?!.*  )/'],
            'last_name' => ['regex:/^[^ ]/', 'regex:/^(?!.*  )/'],
            'email' => ['regex:/^[^ ]/', 'regex:/^(?!.*  )/'],
            'password' => ['regex:/^[^ ]/'], 
        ], [
            'first_name.regex' => 'First name cannot start with a space and cannot contain consecutive spaces.',
            'last_name.regex' => 'Last name cannot start with a space and cannot contain consecutive spaces.',
            'email.regex' => 'Email cannot start with a space and cannot contain consecutive spaces.',
            'password.regex' => 'Password cannot start with a space.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $request->validated();

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'newsletter_subscribed' => $validated['newsletter_subscribed'] ?? false,
            'status' => 'active',
        ]);

        $tokens = $user->generateTokens('user-token', ['*'], 30); 

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        DB::table('user_login_logs')->insert([
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => 'success',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        ActivityLog::log('register', 'auth', "New user {$user->full_name} registered", $user);

        event(new UserRegistered($user));

        $refreshTokenCookie = cookie(
            'client_refresh_token',
            $tokens['refresh_token'],
            30 * 24 * 60, 
            '/',
            null,
            config('app.env') === 'production', 
            true, 
            false, 
            'Lax' 
        );

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Welcome to Casa Vera!',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                ],
                'access_token' => $tokens['access_token'],
            ],
        ], 201)->cookie($refreshTokenCookie);
    }

    public function login(LoginRequest $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'email' => ['regex:/^[^ ]/', 'regex:/^(?!.*  )/'],
            'password' => ['regex:/^[^ ]/'], 
        ], [
            'email.regex' => 'Email cannot start with a space and cannot contain consecutive spaces.',
            'password.regex' => 'Password cannot start with a space.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            
            DB::table('user_login_logs')->insert([
                'user_id' => $user?->id,
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

        if ($user->status !== 'active') {
            $message = match ($user->status) {
                'banned' => 'Your account has been banned. Reason: ' . ($user->ban_reason ?? 'Policy violation'),
                'inactive' => 'Your account is inactive. Please contact support.',
                'pending_verification' => 'Please verify your email address.',
                default => 'Your account is not active.',
            };

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        $tokens = $user->generateTokens('user-token', ['*'], 30); 

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        DB::table('user_login_logs')->insert([
            'user_id' => $user->id,
            'email' => $credentials['email'],
            'status' => 'success',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        event(new UserLogin($user));

        $refreshTokenCookie = cookie(
            'client_refresh_token',
            $tokens['refresh_token'],
            30 * 24 * 60, 
            '/',
            null,
            config('app.env') === 'production', 
            true, 
            false, 
            'Lax' 
        );

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'total_spent' => $user->total_spent,
                    'order_count' => $user->order_count,
                ],
                'access_token' => $tokens['access_token'],
            ],
        ])->cookie($refreshTokenCookie);
    }

    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            try {
                $user->load(['defaultShippingAddress', 'defaultBillingAddress']);
            } catch (\Exception $e) {
                
                \Log::warning('Failed to load user address relationships', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name ?? '',
                        'last_name' => $user->last_name ?? '',
                        'full_name' => $user->full_name ?? '',
                        'email' => $user->email ?? '',
                        'phone' => $user->phone ?? null,
                        'avatar' => $user->avatar ?? null,
                        'address_line_1' => $user->address_line_1 ?? null,
                        'address_line_2' => $user->address_line_2 ?? null,
                        'city' => $user->city ?? null,
                        'province' => $user->province ?? null,
                        'postal_code' => $user->postal_code ?? null,
                        'country' => $user->country ?? null,
                        'total_spent' => $user->total_spent ?? 0,
                        'order_count' => $user->order_count ?? 0,
                        'newsletter_subscribed' => $user->newsletter_subscribed ?? false,
                        'default_shipping_address' => $user->defaultShippingAddress ?? null,
                        'default_billing_address' => $user->defaultBillingAddress ?? null,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Auth me endpoint failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user data.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->revokeAllTokens();

        $cookie = cookie()->forget('client_refresh_token');

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ])->cookie($cookie);
    }

    public function refresh(Request $request): JsonResponse
    {
        
        $refreshToken = $request->cookie('client_refresh_token');

        if (!$refreshToken) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token not found.',
            ], 401);
        }

        $tokenRecord = RefreshToken::findToken($refreshToken);

        if (!$tokenRecord) {
            
            $cookie = cookie()->forget('client_refresh_token');
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired refresh token.',
            ], 401)->cookie($cookie);
        }

        $user = $tokenRecord->tokenable;

        if (!$user || $user->status !== 'active') {
            $tokenRecord->revoke();
            $cookie = cookie()->forget('client_refresh_token');
            return response()->json([
                'success' => false,
                'message' => 'User account is not active.',
            ], 403)->cookie($cookie);
        }

        $tokenRecord->revoke();

        $tokens = $user->generateTokens('user-token', ['*'], 30);

        $refreshTokenCookie = cookie(
            'client_refresh_token',
            $tokens['refresh_token'],
            30 * 24 * 60, 
            '/',
            null,
            config('app.env') === 'production', 
            true, 
            false, 
            'Lax' 
        );

        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $tokens['access_token'],
            ],
        ])->cookie($refreshTokenCookie);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $noSpamSpaces = ['regex:/^[^ ]/', 'regex:/^(?!.*  )/'];

        $validated = $request->validate([
            'first_name' => array_merge(['sometimes', 'string', 'max:100'], $noSpamSpaces),
            'last_name' => array_merge(['sometimes', 'string', 'max:100'], $noSpamSpaces),
            'email' => array_merge(['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id], $noSpamSpaces),
            'phone' => array_merge(['nullable', 'string', 'max:20'], $noSpamSpaces),
            'avatar' => ['nullable', 'string', 'max:255'],
            'address_line_1' => array_merge(['nullable', 'string', 'max:255'], $noSpamSpaces),
            'address_line_2' => array_merge(['nullable', 'string', 'max:255'], $noSpamSpaces),
            'city' => array_merge(['nullable', 'string', 'max:100'], $noSpamSpaces),
            'province' => array_merge(['nullable', 'string', 'max:100'], $noSpamSpaces),
            'postal_code' => array_merge(['nullable', 'string', 'max:20'], $noSpamSpaces),
            'country' => array_merge(['nullable', 'string', 'max:100'], $noSpamSpaces),
            'newsletter_subscribed' => ['sometimes', 'boolean'],
            'sms_notifications' => ['sometimes', 'boolean'],
        ], [
            'regex' => 'The :attribute cannot start with a space and cannot contain consecutive spaces.',
            'email.unique' => 'This email address is already registered.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        $user->update($validated);

        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'address_line_1' => $user->address_line_1,
                    'address_line_2' => $user->address_line_2,
                    'city' => $user->city,
                    'province' => $user->province,
                    'postal_code' => $user->postal_code,
                    'country' => $user->country,
                ],
            ],
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    public function getAccountStats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            $totalOrders = Order::where('user_id', $user->id)->count();

            $totalSpent = Order::where('user_id', $user->id)
                ->where('status', 'delivered')
                ->sum('total');

            $memberSince = $user->created_at
                ? $user->created_at->format('M Y')
                : '';

            return response()->json([
                'success' => true,
                'data' => [
                    'total_orders' => $totalOrders,
                    'total_spent' => (float)$totalSpent, 
                    'member_since' => $memberSince,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get account stats', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch account statistics.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function redirectToGoogle(Request $request)
    {
        $clientId = env('GOOGLE_CLIENT_ID');

        if (!$clientId) {
            return response()->json([
                'success' => false,
                'message' => 'Google OAuth is not configured.',
            ], 500);
        }

        $redirectUri = env('GOOGLE_REDIRECT_URI', url('/api/auth/google/callback'));
        $scope = 'openid email profile';
        $state = Str::random(32); 

        $request->session()->put('google_oauth_state', $state);

        $action = $request->query('action', 'login'); 
        $request->session()->put('google_oauth_action', $action);

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scope,
            'access_type' => 'offline',
            'prompt' => 'select_account', 
            'state' => $state,
        ]);

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;

        return redirect($authUrl);
    }

    public function handleGoogleCallback(Request $request)
    {
        
        $sessionState = $request->session()->get('google_oauth_state');
        $requestState = $request->query('state');

        if (!$sessionState || $sessionState !== $requestState) {
            return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/?error=invalid_state');
        }

        $request->session()->forget('google_oauth_state');

        $action = $request->session()->get('google_oauth_action', 'login');
        $request->session()->forget('google_oauth_action');

        $code = $request->query('code');
        $error = $request->query('error');

        if ($error) {
            return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/?error=' . urlencode($error));
        }

        if (!$code) {
            return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/?error=no_code');
        }

        try {
            
            $clientId = env('GOOGLE_CLIENT_ID');
            $clientSecret = env('GOOGLE_CLIENT_SECRET');
            $redirectUri = env('GOOGLE_REDIRECT_URI', url('/api/auth/google/callback'));

            $tokenResponse = $this->exchangeCodeForToken($code, $clientId, $clientSecret, $redirectUri);

            if (!isset($tokenResponse['access_token'])) {
                return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/?error=token_exchange_failed');
            }

            $userInfo = $this->getGoogleUserInfo($tokenResponse['access_token']);

            if (!$userInfo || !isset($userInfo['email'])) {
                return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/?error=user_info_failed');
            }

            $user = User::where('email', $userInfo['email'])->first();

            if (!$user) {
                
                $nameParts = $this->parseName($userInfo['name'] ?? '');

                $user = User::create([
                    'first_name' => $nameParts['first_name'],
                    'last_name' => $nameParts['last_name'],
                    'email' => $userInfo['email'],
                    'password' => Hash::make(bin2hex(random_bytes(32))), 
                    'avatar' => $userInfo['picture'] ?? null,
                    'status' => 'active',
                    'email_verified_at' => now(), 
                ]);

                ActivityLog::log('register', 'auth', "New user {$user->full_name} registered via Google", $user);

                event(new UserRegistered($user));
            } else {
                
                if ($userInfo['picture'] && !$user->avatar) {
                    $user->avatar = $userInfo['picture'];
                }
                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                }
                $user->save();
            }

            if ($user->status !== 'active') {
                $message = match ($user->status) {
                    'banned' => 'Your account has been banned.',
                    'inactive' => 'Your account is inactive.',
                    'pending_verification' => 'Your account is pending verification.',
                    default => 'Your account is not active.',
                };
                return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/?error=' . urlencode($message));
            }

            $tokens = $user->generateTokens('user-token', ['*'], 30);

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            DB::table('user_login_logs')->insert([
                'user_id' => $user->id,
                'email' => $user->email,
                'status' => 'success',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            event(new UserLogin($user));

            $refreshTokenCookie = cookie(
                'client_refresh_token',
                $tokens['refresh_token'],
                30 * 24 * 60, 
                '/',
                null,
                config('app.env') === 'production',
                true, 
                false, 
                'Lax' 
            );

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $redirectUrl = $frontendUrl . '/auth/google/callback?' . http_build_query([
                'token' => $tokens['access_token'],
                'action' => $action,
            ]);

            return redirect($redirectUrl)->cookie($refreshTokenCookie);

        } catch (\Exception $e) {
            \Log::error('Google OAuth callback failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/?error=' . urlencode('Authentication failed. Please try again.'));
        }
    }

    private function exchangeCodeForToken(string $code, string $clientId, string $clientSecret, string $redirectUri): array
    {
        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);

            if ($response->status() !== 200) {
                throw new \Exception('Failed to exchange code for token. Status: ' . $response->status());
            }

            $data = $response->json();
            if (!$data || !isset($data['access_token'])) {
                throw new \Exception('Invalid token response from Google');
            }

            return $data;
        } catch (\Exception $e) {
            throw new \Exception('Token exchange failed: ' . $e->getMessage());
        }
    }

    private function getGoogleUserInfo(string $accessToken): ?array
    {
        try {
            $response = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if ($response->status() !== 200) {
                throw new \Exception('Failed to get user info. Status: ' . $response->status());
            }

            $data = $response->json();
            if (!$data || !isset($data['email'])) {
                throw new \Exception('Invalid user info response from Google');
            }

            return $data;
        } catch (\Exception $e) {
            throw new \Exception('Failed to get user info: ' . $e->getMessage());
        }
    }

    private function parseName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName), 2);

        return [
            'first_name' => $parts[0] ?? 'User',
            'last_name' => $parts[1] ?? '',
        ];
    }
}
