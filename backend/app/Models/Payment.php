<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'order_id',
        'user_id',
        'payment_method_id',
        'payment_method_name',
        'amount',
        'fee_amount',
        'net_amount',
        'currency',
        'status',
        'payment_details',
        'sender_name',
        'sender_account',
        'reference_number',
        'payment_date',
        'proof_image',
        'proof_notes',
        'verified_at',
        'verified_by_admin_id',
        'verification_notes',
        'refunded_amount',
        'refunded_at',
        'failure_code',
        'failure_reason',
        'expires_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'payment_details' => 'array',
            'amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'payment_date' => 'datetime',
            'verified_at' => 'datetime',
            'refunded_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Admin::class, 'verified_by_admin_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(PaymentStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    // Generate transaction ID
    public static function generateTransactionId(): string
    {
        $prefix = 'TXN';
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}{$timestamp}{$random}";
    }

    // Scopes
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
