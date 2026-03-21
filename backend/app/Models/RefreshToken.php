<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class RefreshToken extends Model
{
    use SoftDeletes;

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

    public static function generateToken($tokenable, int $days = 30): string
    {
        $token = Str::random(64);
        $tokenHash = hash('sha256', $token);

        self::create([
            'tokenable_type' => get_class($tokenable),
            'tokenable_id' => $tokenable->id,
            'token_hash' => $tokenHash, 
            'expires_at' => now()->addDays($days),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $token; 
    }

    public static function findToken(string $token): ?self
    {
        $tokenHash = hash('sha256', $token);

        $token = self::where('token_hash', $tokenHash)
            ->where('expires_at', '>', now())
            ->first();

        if (!$token) {
            $token = self::onlyTrashed()
                ->where('token_hash', $tokenHash)
                ->where('deleted_at', '>', now()->subSeconds(10))
                ->first();
        }

        return $token;
    }

    public function revoke(): void
    {
        $this->delete();
    }

    public static function revokeAllFor($tokenable): void
    {
        self::where('tokenable_type', get_class($tokenable))
            ->where('tokenable_id', $tokenable->id)
            ->delete();
    }

    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', now())->delete();
    }

    public function tokenable()
    {
        return $this->morphTo();
    }
}
