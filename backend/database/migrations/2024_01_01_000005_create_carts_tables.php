<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id', 100)->nullable(); 

            $table->unsignedInteger('item_count')->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->string('coupon_code', 50)->nullable();

            $table->enum('status', ['active', 'converted', 'abandoned', 'expired'])->default('active');
            $table->timestamp('converted_at')->nullable(); 
            $table->timestamp('abandoned_at')->nullable(); 
            $table->timestamp('last_activity_at')->nullable();

            $table->string('currency', 3)->default('PHP');
            $table->text('notes')->nullable();
            
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['session_id', 'status']);
            $table->index(['status', 'last_activity_at']);
            $table->index(['status', 'created_at']); 
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2); 
            $table->decimal('sale_price', 12, 2)->nullable(); 
            $table->decimal('subtotal', 12, 2); 

            $table->string('product_name', 255);
            $table->string('product_sku', 100);
            $table->string('product_image')->nullable();

            $table->json('options')->nullable();
            
            $table->timestamps();
            
            $table->unique(['cart_id', 'product_id']); 
            $table->index(['product_id']);
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            
            $table->unique(['user_id', 'product_id']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
