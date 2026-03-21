<?php

namespace App\Traits;

use App\Models\RefreshToken;
use Illuminate\Support\Facades\Cookie;

trait HasRefreshTokens
{
    
    public function generateTokens(string $tokenName = 'auth-token', array $abilities = ['*'], int $refreshTokenDays = 30): array
    {
        
        $accessToken = $this->createToken($tokenName, $abilities)->plainTextToken;

        $refreshToken = RefreshToken::generateToken($this, $refreshTokenDays);
        
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    public function revokeAllTokens(): void
    {
        
        $this->tokens()->delete();

        RefreshToken::revokeAllFor($this);
    }

    public function refreshTokens()
    {
        return $this->morphMany(RefreshToken::class, 'tokenable');
    }
}
