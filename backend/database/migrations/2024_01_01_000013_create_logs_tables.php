<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * ACTIVITY LOGS & STOCK LOGS TABLES
     * Comprehensive audit trail and inventory tracking
     */
    public function up(): void
    {
        // Activity Logs (general audit trail)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Who performed the action
            $table->string('causer_type', 100)->nullable(); // User, Admin, System
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->string('causer_name', 200)->nullable(); // For historical record
            
            // What was affected
            $table->string('subject_type', 100)->nullable(); // Product, Order, User, etc.
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_name', 255)->nullable(); // For historical record
            
            // Action details
            $table->string('action', 50); // created, updated, deleted, viewed, etc.
            $table->string('module', 50)->nullable(); // products, orders, users, etc.
            $table->text('description')->nullable();
            
            // Changes made (before/after)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('properties')->nullable(); // Additional context
            
            // Request info
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, etc.
            
            $table->timestamps();
            
            // Indexes
            $table->index(['causer_type', 'causer_id']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['action', 'created_at']);
            $table->index(['module', 'created_at']);
            $table->index(['created_at']);
        });

        // Stock Logs (inventory movement tracking)
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Stock change
            $table->integer('quantity_change'); // Positive for additions, negative for deductions
            $table->unsignedInteger('quantity_before');
            $table->unsignedInteger('quantity_after');
            
            // Reason for change
            $table->enum('type', [
                'initial',          // Initial stock setup
                'purchase',         // Stock purchase/restock
                'sale',             // Sold (order placed)
                'return',           // Customer return
                'adjustment',       // Manual adjustment
                'damage',           // Damaged goods
                'theft',            // Theft/loss
                'transfer_in',      // Transfer from another location
                'transfer_out',     // Transfer to another location
                'reserved',         // Reserved for order
                'unreserved',       // Order cancelled, stock returned
                'count_correction'  // Inventory count correction
            ])->default('adjustment');
            
            // Reference
            $table->string('reference_type', 100)->nullable(); // order, adjustment, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number', 100)->nullable(); // Order number, PO number, etc.
            
            // Notes
            $table->text('notes')->nullable();
            
            // Who made the change
            $table->foreignId('admin_id')->nullable()->constrained()->onDelete('set null');
            $table->string('admin_name', 200)->nullable(); // For historical record
            
            // Cost tracking (for FIFO/LIFO)
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['product_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['admin_id']);
        });

        // Order Logs (order-specific activity)
        Schema::create('order_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            $table->string('action', 100); // status_changed, item_added, payment_received, etc.
            $table->text('description');
            
            // Changes
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Who made the change
            $table->string('performed_by_type', 50)->nullable(); // admin, system, customer, courier
            $table->unsignedBigInteger('performed_by_id')->nullable();
            $table->string('performed_by_name', 200)->nullable();
            
            // Notification
            $table->boolean('customer_notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['order_id', 'created_at']);
            $table->index(['action']);
        });

        // Payment Logs
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            $table->string('action', 100); // initiated, submitted, verified, failed, refunded
            $table->text('description')->nullable();
            
            $table->json('request_data')->nullable(); // What was sent
            $table->json('response_data')->nullable(); // What was received
            
            $table->string('performed_by_type', 50)->nullable();
            $table->unsignedBigInteger('performed_by_id')->nullable();
            
            $table->string('ip_address', 45)->nullable();
            
            $table->timestamps();
            
            $table->index(['payment_id', 'created_at']);
            $table->index(['order_id']);
            $table->index(['action']);
        });

        // Admin Login History
        Schema::create('admin_login_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('admin_id')->nullable()->constrained()->onDelete('set null');
            $table->string('email'); // In case admin is deleted
            
            $table->enum('status', ['success', 'failed', 'blocked'])->default('success');
            $table->string('failure_reason', 100)->nullable();
            
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('location', 200)->nullable(); // City, Country from IP
            
            $table->timestamps();
            
            $table->index(['admin_id', 'created_at']);
            $table->index(['email', 'status']);
            $table->index(['ip_address']);
            $table->index(['status', 'created_at']);
        });

        // User Login History
        Schema::create('user_login_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('email');
            
            $table->enum('status', ['success', 'failed', 'blocked'])->default('success');
            $table->string('failure_reason', 100)->nullable();
            
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['email', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_login_logs');
        Schema::dropIfExists('admin_login_logs');
        Schema::dropIfExists('payment_logs');
        Schema::dropIfExists('order_logs');
        Schema::dropIfExists('stock_logs');
        Schema::dropIfExists('activity_logs');
    }
};
