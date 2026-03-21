<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('order_item_id')->nullable()->constrained()->onDelete('set null');

            $table->string('reviewer_name', 200);
            $table->string('reviewer_email');

            $table->unsignedTinyInteger('rating'); 

            $table->string('title', 255)->nullable();
            $table->text('content');

            $table->json('pros')->nullable();
            $table->json('cons')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'spam'])->default('pending');
            $table->boolean('is_verified_purchase')->default(false);

            $table->foreignId('moderated_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('moderated_at')->nullable();
            $table->text('moderation_notes')->nullable();

            $table->boolean('is_featured')->default(false);

            $table->unsignedInteger('helpful_count')->default(0);
            $table->unsignedInteger('not_helpful_count')->default(0);

            $table->string('ip_address', 45)->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'status', 'created_at']);
            $table->index(['user_id']);
            $table->index(['status', 'created_at']);
            $table->index(['rating']);
            $table->index(['is_featured', 'status']);
        });

        Schema::create('review_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            
            $table->string('image_path');
            $table->string('thumbnail_path')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            
            $table->timestamps();
            
            $table->index(['review_id', 'display_order']);
        });

        Schema::create('review_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id', 100)->nullable(); 
            
            $table->boolean('is_helpful');
            $table->string('ip_address', 45)->nullable();
            
            $table->timestamps();

            $table->unique(['review_id', 'user_id']);
            $table->index(['review_id', 'session_id']);
        });

        Schema::create('review_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            
            $table->text('content');
            $table->boolean('is_visible')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['review_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_replies');
        Schema::dropIfExists('review_votes');
        Schema::dropIfExists('review_images');
        Schema::dropIfExists('reviews');
    }
};
