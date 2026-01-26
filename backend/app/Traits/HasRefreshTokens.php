<?php

namespace App\Traits;

use App\Models\RefreshToken;
use Illuminate\Support\Facades\Cookie;

trait HasRefreshTokens
{
    /**
     * Generate access and refresh tokens
     * 
     * @param string $tokenName Name for the access token
     * @param array $abilities Abilities for the access token
     * @param int $refreshTokenDays Number of days for refresh token (default: 30)
     * @return array ['access_token' => string, 'refresh_token' => string]
     */
    public function generateTokens(string $tokenName = 'auth-token', array $abilities = ['*'], int $refreshTokenDays = 30): array
    {
        // Create short-lived access token (30 minutes)
        $accessToken = $this->createToken($tokenName, $abilities)->plainTextToken;
        
        // Create long-lived refresh token (30 days by default)
        $refreshToken = RefreshToken::generateToken($this, $refreshTokenDays);
        
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    /**
     * Revoke all tokens (access and refresh)
     */
    public function revokeAllTokens(): void
    {
        // Revoke all Sanctum tokens
        $this->tokens()->delete();
        
        // Revoke all refresh tokens
        RefreshToken::revokeAllFor($this);
    }

    /**
     * Get refresh tokens relationship
     */
    public function refreshTokens()
    {
        return $this->morphMany(RefreshToken::class, 'tokenable');
    }
}
