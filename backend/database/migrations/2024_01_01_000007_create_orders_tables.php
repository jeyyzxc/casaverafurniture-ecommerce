<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_number', 50)->unique(); 

            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_email');
            $table->string('customer_phone', 20)->nullable();
            $table->string('customer_name', 200);

            $table->enum('status', [
                'pending',          
                'confirmed',        
                'processing',       
                'ready_for_pickup', 
                'shipped',          
                'out_for_delivery', 
                'delivered',        
                'cancelled',        
                'refunded',         
                'partially_refunded', 
                'failed'            
            ])->default('pending');

            $table->string('billing_name', 200);
            $table->text('billing_address_line_1');
            $table->text('billing_address_line_2')->nullable();
            $table->string('billing_city', 100);
            $table->string('billing_province', 100);
            $table->string('billing_postal_code', 20);
            $table->string('billing_country', 100)->default('Philippines');
            $table->string('billing_phone', 20)->nullable();

            $table->string('shipping_name', 200);
            $table->text('shipping_address_line_1');
            $table->text('shipping_address_line_2')->nullable();
            $table->string('shipping_city', 100);
            $table->string('shipping_province', 100);
            $table->string('shipping_postal_code', 20);
            $table->string('shipping_country', 100)->default('Philippines');
            $table->string('shipping_phone', 20)->nullable();

            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('currency', 3)->default('PHP');

            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->string('coupon_code', 50)->nullable();

            $table->foreignId('shipping_zone_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('shipping_rate_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('courier_id')->nullable()->constrained()->onDelete('set null');
            $table->string('shipping_method', 100)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->string('tracking_url')->nullable();

            $table->date('estimated_delivery_date')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->string('payment_status', 50)->default('pending');

            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->string('source', 50)->default('web'); 
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['order_number']);
            $table->index(['customer_email']);
            $table->index(['payment_status']);
            $table->index(['tracking_number']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

            $table->string('product_name', 255);
            $table->string('product_sku', 100);
            $table->string('product_image')->nullable();
            $table->text('product_description')->nullable();
            $table->json('product_options')->nullable(); 

            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])->default('pending');

            $table->string('tracking_number', 100)->nullable();
            
            $table->timestamps();
            
            $table->index(['order_id']);
            $table->index(['product_id']);
            $table->index(['status']);
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            $table->string('status', 50);
            $table->string('previous_status', 50)->nullable();
            $table->text('notes')->nullable();

            $table->enum('changed_by_type', ['system', 'admin', 'customer', 'courier'])->default('system');
            $table->foreignId('admin_id')->nullable()->constrained()->onDelete('set null');

            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->boolean('notification_sent')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['order_id', 'created_at']);
            $table->index(['status']);
        });

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

    public function down(): void
    {
        Schema::dropIfExists('order_notes');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
