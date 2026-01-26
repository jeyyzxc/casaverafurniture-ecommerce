<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CART & CHECKOUT TABLES
     * Persistent cart system with guest support
     */
    public function up(): void
    {
        // Carts Table (supports both authenticated users and guests)
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            
            // User or session identifier (one must be set)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id', 100)->nullable(); // For guest carts
            
            // Cart totals (cached for performance)
            $table->unsignedInteger('item_count')->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            
            // Applied promotion (FK added in promotions migration)
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->string('coupon_code', 50)->nullable();
            
            // Cart status
            $table->enum('status', ['active', 'converted', 'abandoned', 'expired'])->default('active');
            $table->timestamp('converted_at')->nullable(); // When cart became an order
            $table->timestamp('abandoned_at')->nullable(); // When marked as abandoned
            $table->timestamp('last_activity_at')->nullable();
            
            // Metadata
            $table->string('currency', 3)->default('PHP');
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['session_id', 'status']);
            $table->index(['status', 'last_activity_at']);
            $table->index(['status', 'created_at']); // For abandoned cart queries
        });

        // Cart Items Table
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Quantity and pricing
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2); // Price at time of adding
            $table->decimal('sale_price', 12, 2)->nullable(); // If product was on sale
            $table->decimal('subtotal', 12, 2); // quantity * price
            
            // Product snapshot (in case product is modified/deleted)
            $table->string('product_name', 255);
            $table->string('product_sku', 100);
            $table->string('product_image')->nullable();
            
            // Custom options if any (stored as JSON)
            $table->json('options')->nullable();
            
            $table->timestamps();
            
            $table->unique(['cart_id', 'product_id']); // One entry per product per cart
            $table->index(['product_id']);
        });

        // Saved for Later / Wishlist
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            
            $table->unique(['user_id', 'product_id']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
