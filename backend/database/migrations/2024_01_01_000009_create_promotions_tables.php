<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();

            $table->string('name', 200);
            $table->string('code', 50)->unique(); 
            $table->text('description')->nullable();

            $table->enum('discount_type', ['percentage', 'fixed', 'free_shipping', 'buy_x_get_y'])->default('percentage');
            $table->decimal('discount_value', 12, 2); 
            $table->decimal('max_discount_amount', 12, 2)->nullable(); 

            $table->unsignedInteger('buy_quantity')->nullable();
            $table->unsignedInteger('get_quantity')->nullable();
            $table->decimal('get_discount_percentage', 5, 2)->nullable(); 

            $table->enum('applies_to', ['all', 'specific_products', 'specific_categories', 'specific_collections'])->default('all');
            $table->json('applicable_product_ids')->nullable();
            $table->json('applicable_category_ids')->nullable();
            $table->json('applicable_collection_ids')->nullable();

            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->decimal('max_order_amount', 12, 2)->nullable();
            $table->unsignedInteger('min_quantity')->nullable();

            $table->unsignedInteger('usage_limit')->nullable(); 
            $table->unsignedInteger('usage_limit_per_user')->nullable(); 
            $table->unsignedInteger('used_count')->default(0);

            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('ends_at')->nullable();

            $table->boolean('first_order_only')->default(false);
            $table->json('allowed_user_ids')->nullable(); 
            $table->json('allowed_user_emails')->nullable(); 

            $table->boolean('is_active')->default(true);
            $table->boolean('is_visible')->default(true); 

            $table->boolean('auto_apply')->default(false);
            $table->unsignedInteger('priority')->default(0); 

            $table->boolean('combinable_with_other_promotions')->default(false);

            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code']);
            $table->index(['is_active', 'starts_at', 'ends_at']);
            $table->index(['discount_type']);
            $table->index(['auto_apply', 'is_active']);
        });

        Schema::create('promotion_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            $table->decimal('discount_amount', 12, 2); 
            $table->string('code_used', 50);
            
            $table->timestamps();
            
            $table->index(['promotion_id', 'user_id']);
            $table->index(['user_id']);
            $table->index(['order_id']);
        });

        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 200);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable();

            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('ends_at')->useCurrent();

            $table->decimal('discount_percentage', 5, 2)->default(0);

            $table->unsignedInteger('max_units_per_product')->nullable();
            $table->unsignedInteger('max_units_per_user')->nullable();
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_active', 'starts_at', 'ends_at']);
        });

        Schema::create('flash_sale_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            $table->decimal('sale_price', 12, 2);
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->unsignedInteger('quantity_available')->nullable();
            $table->unsignedInteger('quantity_sold')->default(0);
            
            $table->timestamps();
            
            $table->unique(['flash_sale_id', 'product_id']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('set null');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
        });
        Schema::dropIfExists('flash_sale_products');
        Schema::dropIfExists('flash_sales');
        Schema::dropIfExists('promotion_usages');
        Schema::dropIfExists('promotions');
    }
};
