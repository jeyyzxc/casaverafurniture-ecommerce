<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * PAYMENTS TABLES
     * Advanced mock payment system with GCash, Bank Transfer, COD support
     */
    public function up(): void
    {
        // Payment Methods Table (configurable payment options)
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 100); // "GCash", "Bank Transfer", "COD"
            $table->string('code', 50)->unique(); // "gcash", "bank_transfer", "cod"
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            
            // Payment type
            $table->enum('type', ['ewallet', 'bank_transfer', 'cod', 'credit_card', 'debit_card', 'other'])->default('other');
            
            // Processing
            $table->boolean('requires_verification')->default(false); // Needs manual verification
            $table->boolean('requires_proof_of_payment')->default(false);
            
            // Fees
            $table->decimal('fee_fixed', 10, 2)->default(0);
            $table->decimal('fee_percentage', 5, 2)->default(0);
            
            // Limits
            $table->decimal('min_amount', 12, 2)->nullable();
            $table->decimal('max_amount', 12, 2)->nullable();
            
            // Instructions for customer
            $table->text('payment_instructions')->nullable();
            
            // Bank/E-wallet specific details
            $table->json('account_details')->nullable(); // Bank name, account number, etc.
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_active', 'display_order']);
            $table->index(['type']);
        });

        // Payments Table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // Transaction reference
            $table->string('transaction_id', 100)->unique(); // TXN-XXXXXX format
            $table->string('external_transaction_id', 255)->nullable(); // ID from payment gateway
            
            // Related order
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            // Payment method
            $table->foreignId('payment_method_id')->nullable()->constrained()->onDelete('set null');
            $table->string('payment_method_name', 100); // Stored for history
            
            // Amount
            $table->decimal('amount', 12, 2);
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 12, 2); // amount - fee
            $table->string('currency', 3)->default('PHP');
            
            // Status
            $table->enum('status', [
                'pending',          // Awaiting payment
                'processing',       // Payment being processed
                'awaiting_verification', // Submitted, needs admin verification
                'confirmed',        // Payment confirmed
                'failed',           // Payment failed
                'cancelled',        // Cancelled by user
                'refunded',         // Fully refunded
                'partially_refunded', // Partially refunded
                'expired'           // Payment window expired
            ])->default('pending');
            
            // Payment details (method-specific)
            $table->json('payment_details')->nullable();
            
            // For GCash/Bank Transfer
            $table->string('sender_name', 200)->nullable();
            $table->string('sender_account', 100)->nullable(); // Last 4 digits or reference
            $table->string('reference_number', 100)->nullable();
            $table->timestamp('payment_date')->nullable(); // When customer made payment
            
            // Proof of payment
            $table->string('proof_image')->nullable();
            $table->text('proof_notes')->nullable();
            
            // Verification
            $table->foreignId('verified_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            
            // Refund info
            $table->decimal('refunded_amount', 12, 2)->default(0);
            $table->text('refund_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();
            
            // Failure info
            $table->string('failure_code', 50)->nullable();
            $table->text('failure_reason')->nullable();
            
            // Expiration (for pending payments)
            $table->timestamp('expires_at')->nullable();
            
            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['order_id']);
            $table->index(['user_id']);
            $table->index(['status', 'created_at']);
            $table->index(['payment_method_id', 'status']);
            $table->index(['reference_number']);
            $table->index(['expires_at']);
        });

        // Payment Status History
        Schema::create('payment_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            
            $table->string('status', 50);
            $table->string('previous_status', 50)->nullable();
            $table->text('notes')->nullable();
            
            $table->enum('changed_by_type', ['system', 'admin', 'customer', 'gateway'])->default('system');
            $table->foreignId('admin_id')->nullable()->constrained()->onDelete('set null');
            
            $table->timestamps();
            
            $table->index(['payment_id', 'created_at']);
        });

        // Refunds Table (for tracking partial refunds)
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            
            $table->string('refund_number', 50)->unique(); // REF-XXXXXX
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('PHP');
            
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            
            $table->enum('reason', [
                'customer_request',
                'order_cancelled',
                'item_not_available',
                'defective_item',
                'wrong_item',
                'duplicate_payment',
                'other'
            ])->default('customer_request');
            $table->text('reason_details')->nullable();
            
            // Refund method
            $table->string('refund_method', 100)->nullable(); // Same as payment or different
            $table->json('refund_details')->nullable(); // Account to refund to
            
            // Processing
            $table->foreignId('processed_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['payment_id']);
            $table->index(['order_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payment_status_history');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');
    }
};
