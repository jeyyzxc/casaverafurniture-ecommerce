<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class RefreshToken extends Model
{
    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'token_hash',
        'expires_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'token_hash',
    ];

    /**
     * Generate a new refresh token
     */
    public static function generateToken($tokenable, int $days = 30): string
    {
        $token = Str::random(64);
        $tokenHash = hash('sha256', $token);

        self::create([
            'tokenable_type' => get_class($tokenable),
            'tokenable_id' => $tokenable->id,
            'token_hash' => $tokenHash, // Only store hash, never plain token
            'expires_at' => now()->addDays($days),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $token; // Return plain token only for initial response
    }

    /**
     * Find token by plain text token
     */
    public static function findToken(string $token): ?self
    {
        $tokenHash = hash('sha256', $token);
        
        return self::where('token_hash', $tokenHash)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Revoke token
     */
    public function revoke(): void
    {
        $this->delete();
    }

    /**
     * Revoke all tokens for a tokenable
     */
    public static function revokeAllFor($tokenable): void
    {
        self::where('tokenable_type', get_class($tokenable))
            ->where('tokenable_id', $tokenable->id)
            ->delete();
    }

    /**
     * Clean up expired tokens
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', now())->delete();
    }

    /**
     * Relationship to tokenable (User or Admin)
     */
    public function tokenable()
    {
        return $this->morphTo();
    }
}
