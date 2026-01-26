<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'icon',
        'requires_verification',
        'requires_proof_of_payment',
        'fee_fixed',
        'fee_percentage',
        'min_amount',
        'max_amount',
        'payment_instructions',
        'account_details',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'account_details' => 'array',
            'requires_verification' => 'boolean',
            'requires_proof_of_payment' => 'boolean',
            'fee_fixed' => 'decimal:2',
            'fee_percentage' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function calculateFee(float $amount): float
    {
        $percentageFee = $amount * ($this->fee_percentage / 100);
        return $this->fee_fixed + $percentageFee;
    }
}
