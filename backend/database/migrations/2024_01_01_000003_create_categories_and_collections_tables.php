<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CATEGORIES & COLLECTIONS TABLES
     * Hierarchical category structure with collections support
     */
    public function up(): void
    {
        // Categories Table (supports parent-child hierarchy)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            
            // Parent category for subcategories (null = main category)
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('set null');
            
            // Category Information
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 7)->default('#c9a050'); // Hex color code
            
            // Display settings
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('show_in_menu')->default(true);
            $table->boolean('show_in_homepage')->default(false);
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            // Stats (cached for performance)
            $table->unsignedInteger('product_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['parent_id']);
            $table->index(['is_visible', 'display_order']);
            $table->index(['show_in_menu']);
        });

        // Collections Table (for curated product groups like "New Arrivals", "Best Sellers")
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('banner_image')->nullable();
            
            // Collection type
            $table->enum('type', ['manual', 'automated'])->default('manual');
            // For automated collections, store rules as JSON
            $table->json('rules')->nullable();
            
            // Display settings
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('show_in_homepage')->default(false);
            
            // Validity period (for seasonal collections)
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            // Stats
            $table->unsignedInteger('product_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_visible', 'display_order']);
            $table->index(['type']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
        Schema::dropIfExists('categories');
    }
};
