<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * PRODUCT REVIEWS TABLE
     * Customer reviews with ratings and verification
     */
    public function up(): void
    {
        // Product Reviews
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('order_item_id')->nullable()->constrained()->onDelete('set null');
            
            // Reviewer info (in case user is deleted)
            $table->string('reviewer_name', 200);
            $table->string('reviewer_email');
            
            // Rating
            $table->unsignedTinyInteger('rating'); // 1-5 stars
            
            // Review content
            $table->string('title', 255)->nullable();
            $table->text('content');
            
            // Pros and cons
            $table->json('pros')->nullable();
            $table->json('cons')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'spam'])->default('pending');
            $table->boolean('is_verified_purchase')->default(false);
            
            // Moderation
            $table->foreignId('moderated_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('moderated_at')->nullable();
            $table->text('moderation_notes')->nullable();
            
            // Featured
            $table->boolean('is_featured')->default(false);
            
            // Helpfulness
            $table->unsignedInteger('helpful_count')->default(0);
            $table->unsignedInteger('not_helpful_count')->default(0);
            
            // Source
            $table->string('ip_address', 45)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['product_id', 'status', 'created_at']);
            $table->index(['user_id']);
            $table->index(['status', 'created_at']);
            $table->index(['rating']);
            $table->index(['is_featured', 'status']);
        });

        // Review Images
        Schema::create('review_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            
            $table->string('image_path');
            $table->string('thumbnail_path')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            
            $table->timestamps();
            
            $table->index(['review_id', 'display_order']);
        });

        // Review Helpfulness Votes
        Schema::create('review_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id', 100)->nullable(); // For guest votes
            
            $table->boolean('is_helpful');
            $table->string('ip_address', 45)->nullable();
            
            $table->timestamps();
            
            // Prevent duplicate votes
            $table->unique(['review_id', 'user_id']);
            $table->index(['review_id', 'session_id']);
        });

        // Admin Replies to Reviews
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_replies');
        Schema::dropIfExists('review_votes');
        Schema::dropIfExists('review_images');
        Schema::dropIfExists('reviews');
    }
};
