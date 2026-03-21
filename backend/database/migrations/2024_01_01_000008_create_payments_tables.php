<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 100); 
            $table->string('code', 50)->unique(); 
            $table->text('description')->nullable();
            $table->string('icon')->nullable();

            $table->enum('type', ['ewallet', 'bank_transfer', 'cod', 'credit_card', 'debit_card', 'other'])->default('other');

            $table->boolean('requires_verification')->default(false); 
            $table->boolean('requires_proof_of_payment')->default(false);

            $table->decimal('fee_fixed', 10, 2)->default(0);
            $table->decimal('fee_percentage', 5, 2)->default(0);

            $table->decimal('min_amount', 12, 2)->nullable();
            $table->decimal('max_amount', 12, 2)->nullable();

            $table->text('payment_instructions')->nullable();

            $table->json('account_details')->nullable(); 

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_active', 'display_order']);
            $table->index(['type']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->string('transaction_id', 100)->unique(); 
            $table->string('external_transaction_id', 255)->nullable(); 

            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            $table->foreignId('payment_method_id')->nullable()->constrained()->onDelete('set null');
            $table->string('payment_method_name', 100); 

            $table->decimal('amount', 12, 2);
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 12, 2); 
            $table->string('currency', 3)->default('PHP');

            $table->enum('status', [
                'pending',          
                'processing',       
                'awaiting_verification', 
                'confirmed',        
                'failed',           
                'cancelled',        
                'refunded',         
                'partially_refunded', 
                'expired'           
            ])->default('pending');

            $table->json('payment_details')->nullable();

            $table->string('sender_name', 200)->nullable();
            $table->string('sender_account', 100)->nullable(); 
            $table->string('reference_number', 100)->nullable();
            $table->timestamp('payment_date')->nullable(); 

            $table->string('proof_image')->nullable();
            $table->text('proof_notes')->nullable();

            $table->foreignId('verified_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();

            $table->decimal('refunded_amount', 12, 2)->default(0);
            $table->text('refund_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->string('failure_code', 50)->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamp('expires_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id']);
            $table->index(['user_id']);
            $table->index(['status', 'created_at']);
            $table->index(['payment_method_id', 'status']);
            $table->index(['reference_number']);
            $table->index(['expires_at']);
        });

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

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            
            $table->string('refund_number', 50)->unique(); 
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

            $table->string('refund_method', 100)->nullable(); 
            $table->json('refund_details')->nullable(); 

            $table->foreignId('processed_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['payment_id']);
            $table->index(['order_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payment_status_history');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');
    }
};
