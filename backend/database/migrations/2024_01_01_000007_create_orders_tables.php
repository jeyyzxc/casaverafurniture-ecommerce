<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * ORDERS & ORDER TRACKING TABLES
     * Complete order management with status history
     */
    public function up(): void
    {
        // Orders Table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Order reference
            $table->string('order_number', 50)->unique(); // CV-XXXXXX format
            
            // Customer
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_email');
            $table->string('customer_phone', 20)->nullable();
            $table->string('customer_name', 200);
            
            // Order Status
            $table->enum('status', [
                'pending',          // Just placed, awaiting payment confirmation
                'confirmed',        // Payment confirmed, ready for processing
                'processing',       // Being prepared
                'ready_for_pickup', // Ready for courier pickup
                'shipped',          // Handed to courier
                'out_for_delivery', // On delivery vehicle
                'delivered',        // Successfully delivered
                'cancelled',        // Cancelled by customer or admin
                'refunded',         // Full refund processed
                'partially_refunded', // Partial refund
                'failed'            // Payment or delivery failed
            ])->default('pending');
            
            // Billing Address
            $table->string('billing_name', 200);
            $table->text('billing_address_line_1');
            $table->text('billing_address_line_2')->nullable();
            $table->string('billing_city', 100);
            $table->string('billing_province', 100);
            $table->string('billing_postal_code', 20);
            $table->string('billing_country', 100)->default('Philippines');
            $table->string('billing_phone', 20)->nullable();
            
            // Shipping Address
            $table->string('shipping_name', 200);
            $table->text('shipping_address_line_1');
            $table->text('shipping_address_line_2')->nullable();
            $table->string('shipping_city', 100);
            $table->string('shipping_province', 100);
            $table->string('shipping_postal_code', 20);
            $table->string('shipping_country', 100)->default('Philippines');
            $table->string('shipping_phone', 20)->nullable();
            
            // Pricing
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('currency', 3)->default('PHP');
            
            // Discount/Promotion (FK added in promotions migration)
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->string('coupon_code', 50)->nullable();
            
            // Shipping
            $table->foreignId('shipping_zone_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('shipping_rate_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('courier_id')->nullable()->constrained()->onDelete('set null');
            $table->string('shipping_method', 100)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->string('tracking_url')->nullable();
            
            // Delivery estimates
            $table->date('estimated_delivery_date')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // Payment reference (linked to payments table)
            $table->string('payment_status', 50)->default('pending');
            
            // Admin notes
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            
            // Source/channel
            $table->string('source', 50)->default('web'); // web, mobile, phone, admin
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Important timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['order_number']);
            $table->index(['customer_email']);
            $table->index(['payment_status']);
            $table->index(['tracking_number']);
        });

        // Order Items Table
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            
            // Product snapshot (preserved even if product is deleted)
            $table->string('product_name', 255);
            $table->string('product_sku', 100);
            $table->string('product_image')->nullable();
            $table->text('product_description')->nullable();
            $table->json('product_options')->nullable(); // Size, color, etc.
            
            // Quantity and pricing
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            
            // Item status (for partial shipments)
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])->default('pending');
            
            // For individual item tracking
            $table->string('tracking_number', 100)->nullable();
            
            $table->timestamps();
            
            $table->index(['order_id']);
            $table->index(['product_id']);
            $table->index(['status']);
        });

        // Order Status History (for real-time tracking)
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            $table->string('status', 50);
            $table->string('previous_status', 50)->nullable();
            $table->text('notes')->nullable();
            
            // Who made the change
            $table->enum('changed_by_type', ['system', 'admin', 'customer', 'courier'])->default('system');
            $table->foreignId('admin_id')->nullable()->constrained()->onDelete('set null');
            
            // Location data (for shipment tracking)
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Email notification sent?
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['order_id', 'created_at']);
            $table->index(['status']);
        });

        // Order Notes (internal communication)
        Schema::create('order_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained()->onDelete('set null');
            
            $table->text('note');
            $table->boolean('is_customer_visible')->default(false);
            
            $table->timestamps();
            
            $table->index(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_notes');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
