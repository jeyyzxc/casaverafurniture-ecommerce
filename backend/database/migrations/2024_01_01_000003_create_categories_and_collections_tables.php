<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('set null');

            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 7)->default('#c9a050'); 

            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('show_in_menu')->default(true);
            $table->boolean('show_in_homepage')->default(false);

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            $table->unsignedInteger('product_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['parent_id']);
            $table->index(['is_visible', 'display_order']);
            $table->index(['show_in_menu']);
        });

        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('banner_image')->nullable();

            $table->enum('type', ['manual', 'automated'])->default('manual');
            
            $table->json('rules')->nullable();

            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('show_in_homepage')->default(false);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->unsignedInteger('product_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_visible', 'display_order']);
            $table->index(['type']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
        Schema::dropIfExists('categories');
    }
};
