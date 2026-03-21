<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('sku', 100)->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');

            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable(); 
            $table->timestamp('sale_starts_at')->nullable();
            $table->timestamp('sale_ends_at')->nullable();

            $table->integer('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'backorder', 'preorder'])->default('in_stock');
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_backorder')->default(false);

            $table->enum('status', ['active', 'hidden', 'draft', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(true);
            $table->boolean('is_bestseller')->default(false);

            $table->string('dimensions', 100)->nullable(); 
            $table->decimal('weight', 8, 2)->nullable(); 
            $table->string('material', 255)->nullable();
            $table->string('color', 100)->nullable();

            $table->json('attributes')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('order_count')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->unsignedInteger('review_count')->default(0);

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id']);
            $table->index(['status', 'is_featured']);
            $table->index(['status', 'published_at']);
            $table->index(['stock_status']);
            $table->index(['price']);
            $table->index(['sale_price']);
            $table->index(['is_new']);
            $table->index(['is_bestseller']);

        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->string('image_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            $table->index(['product_id', 'is_primary']);
            $table->index(['product_id', 'display_order']);
        });

        Schema::create('collection_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->unique(['collection_id', 'product_id']);
            $table->index(['collection_id', 'display_order']);
        });

        Schema::create('related_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('related_product_id')->constrained('products')->onDelete('cascade');
            $table->enum('relation_type', ['related', 'upsell', 'cross_sell'])->default('related');
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'related_product_id', 'relation_type'], 'related_products_unique');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->timestamps();
        });

        Schema::create('product_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('related_products');
        Schema::dropIfExists('collection_product');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
    }
};
