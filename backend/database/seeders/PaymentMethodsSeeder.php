<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Seed payment methods.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $paymentMethods = [
            [
                'name' => 'GCash',
                'code' => 'gcash',
                'description' => 'Pay using GCash e-wallet. Fast and convenient mobile payment.',
                'icon' => '/images/payments/gcash.png',
                'type' => 'ewallet',
                'requires_verification' => true,
                'requires_proof_of_payment' => true,
                'fee_fixed' => 0,
                'fee_percentage' => 0,
                'min_amount' => 100,
                'max_amount' => 100000,
                'payment_instructions' => "1. Open your GCash app\n2. Tap 'Send Money'\n3. Enter our GCash number: 0917-XXX-XXXX\n4. Enter the exact amount shown\n5. Add your Order Number as reference\n6. Screenshot the receipt\n7. Upload proof of payment below",
                'account_details' => json_encode([
                    'account_name' => 'Casa Vera Furniture',
                    'account_number' => '0917-XXX-XXXX',
                ]),
                'is_active' => true,
                'display_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Maya',
                'code' => 'maya',
                'description' => 'Pay using Maya (PayMaya) e-wallet.',
                'icon' => '/images/payments/maya.png',
                'type' => 'ewallet',
                'requires_verification' => true,
                'requires_proof_of_payment' => true,
                'fee_fixed' => 0,
                'fee_percentage' => 0,
                'min_amount' => 100,
                'max_amount' => 100000,
                'payment_instructions' => "1. Open your Maya app\n2. Tap 'Send Money'\n3. Enter our Maya number: 0918-XXX-XXXX\n4. Enter the exact amount shown\n5. Add your Order Number as reference\n6. Screenshot the receipt\n7. Upload proof of payment below",
                'account_details' => json_encode([
                    'account_name' => 'Casa Vera Furniture',
                    'account_number' => '0918-XXX-XXXX',
                ]),
                'is_active' => true,
                'display_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Bank Transfer (BDO)',
                'code' => 'bank_bdo',
                'description' => 'Pay via BDO bank transfer or deposit.',
                'icon' => '/images/payments/bdo.png',
                'type' => 'bank_transfer',
                'requires_verification' => true,
                'requires_proof_of_payment' => true,
                'fee_fixed' => 0,
                'fee_percentage' => 0,
                'min_amount' => 1000,
                'max_amount' => 500000,
                'payment_instructions' => "Bank: BDO Unibank\nAccount Name: Casa Vera Furniture Trading\nAccount Number: 0012-3456-7890\n\n1. Transfer the exact amount via online banking or deposit\n2. Use your Order Number as reference\n3. Keep your receipt/screenshot\n4. Upload proof of payment below",
                'account_details' => json_encode([
                    'bank_name' => 'BDO Unibank',
                    'account_name' => 'Casa Vera Furniture Trading',
                    'account_number' => '0012-3456-7890',
                    'branch' => 'Makati Main Branch',
                ]),
                'is_active' => true,
                'display_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Bank Transfer (BPI)',
                'code' => 'bank_bpi',
                'description' => 'Pay via BPI bank transfer or deposit.',
                'icon' => '/images/payments/bpi.png',
                'type' => 'bank_transfer',
                'requires_verification' => true,
                'requires_proof_of_payment' => true,
                'fee_fixed' => 0,
                'fee_percentage' => 0,
                'min_amount' => 1000,
                'max_amount' => 500000,
                'payment_instructions' => "Bank: Bank of the Philippine Islands (BPI)\nAccount Name: Casa Vera Furniture Trading\nAccount Number: 1234-5678-90\n\n1. Transfer the exact amount via online banking or deposit\n2. Use your Order Number as reference\n3. Keep your receipt/screenshot\n4. Upload proof of payment below",
                'account_details' => json_encode([
                    'bank_name' => 'Bank of the Philippine Islands',
                    'account_name' => 'Casa Vera Furniture Trading',
                    'account_number' => '1234-5678-90',
                    'branch' => 'BGC Branch',
                ]),
                'is_active' => true,
                'display_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Cash on Delivery',
                'code' => 'cod',
                'description' => 'Pay when your order arrives. Available for Metro Manila and select areas.',
                'icon' => '/images/payments/cod.png',
                'type' => 'cod',
                'requires_verification' => false,
                'requires_proof_of_payment' => false,
                'fee_fixed' => 100, // COD handling fee
                'fee_percentage' => 0,
                'min_amount' => 1000,
                'max_amount' => 50000, // COD limit
                'payment_instructions' => "• Prepare the exact amount for faster delivery\n• Our delivery partner will collect payment upon delivery\n• You will receive a receipt after payment\n• COD fee of ₱100 applies",
                'account_details' => null,
                'is_active' => true,
                'display_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'PayPal',
                'code' => 'paypal',
                'description' => 'Pay securely with PayPal. Fast, safe, and trusted worldwide.',
                'icon' => '/images/payments/paypal.png',
                'type' => 'other',
                'requires_verification' => false,
                'requires_proof_of_payment' => false,
                'fee_fixed' => 0,
                'fee_percentage' => 3.5, // PayPal fee
                'min_amount' => 500,
                'max_amount' => 200000,
                'payment_instructions' => "1. Click 'Pay with PayPal' button\n2. You will be redirected to PayPal login\n3. Log in to your PayPal account\n4. Review and confirm payment\n5. You will be redirected back to complete your order",
                'account_details' => json_encode([
                    'paypal_email' => 'payments@casavera.com',
                    'merchant_id' => 'CASAVERA-PH',
                ]),
                'is_active' => true,
                'display_order' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Credit/Debit Card',
                'code' => 'card',
                'description' => 'Pay with Visa, Mastercard, or other major credit/debit cards.',
                'icon' => '/images/payments/card.png',
                'type' => 'credit_card',
                'requires_verification' => false,
                'requires_proof_of_payment' => false,
                'fee_fixed' => 0,
                'fee_percentage' => 2.5,
                'min_amount' => 1000,
                'max_amount' => 500000,
                'payment_instructions' => "1. Enter your card details securely\n2. Card number, expiry date, and CVV required\n3. Billing address must match card\n4. Payment is processed securely via Stripe\n5. You will receive instant confirmation",
                'account_details' => json_encode([
                    'processor' => 'Stripe',
                    'supported_cards' => ['Visa', 'Mastercard', 'American Express', 'JCB'],
                ]),
                'is_active' => true,
                'display_order' => 7,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Bank Transfer (Metrobank)',
                'code' => 'bank_metrobank',
                'description' => 'Pay via Metrobank bank transfer or deposit.',
                'icon' => '/images/payments/metrobank.png',
                'type' => 'bank_transfer',
                'requires_verification' => true,
                'requires_proof_of_payment' => true,
                'fee_fixed' => 0,
                'fee_percentage' => 0,
                'min_amount' => 1000,
                'max_amount' => 500000,
                'payment_instructions' => "Bank: Metrobank\nAccount Name: Casa Vera Furniture Trading\nAccount Number: 9876-5432-10\n\n1. Transfer the exact amount via online banking or deposit\n2. Use your Order Number as reference\n3. Keep your receipt/screenshot\n4. Upload proof of payment below",
                'account_details' => json_encode([
                    'bank_name' => 'Metrobank',
                    'account_name' => 'Casa Vera Furniture Trading',
                    'account_number' => '9876-5432-10',
                    'branch' => 'Ortigas Center Branch',
                ]),
                'is_active' => true,
                'display_order' => 8,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert payment methods only if they don't exist
        foreach ($paymentMethods as $method) {
            DB::table('payment_methods')->updateOrInsert(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
