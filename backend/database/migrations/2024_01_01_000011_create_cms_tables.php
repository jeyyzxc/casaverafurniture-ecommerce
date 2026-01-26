<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CMS & CONTENT MANAGEMENT TABLES
     * Homepage sections, banners, pages, and site settings
     */
    public function up(): void
    {
        // Homepage Sections (configurable homepage blocks)
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('title', 255)->nullable();
            $table->text('subtitle')->nullable();
            $table->longText('content')->nullable();
            
            // Section type
            $table->enum('type', [
                'hero',           // Main hero banner
                'featured',       // Featured products
                'categories',     // Category showcase
                'collections',    // Collection showcase
                'new_arrivals',   // New products
                'bestsellers',    // Best selling products
                'sale',           // Sale items
                'testimonials',   // Customer testimonials
                'newsletter',     // Newsletter signup
                'banner',         // Promotional banner
                'custom'          // Custom HTML content
            ])->default('custom');
            
            // Associated data
            $table->json('settings')->nullable(); // Type-specific settings
            $table->json('product_ids')->nullable(); // For manual product selection
            $table->unsignedInteger('product_limit')->default(8);
            
            // Display
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->string('background_color', 7)->nullable();
            $table->string('background_image')->nullable();
            
            // Validity
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_visible', 'display_order']);
            $table->index(['type']);
        });

        // Banners (promotional banners throughout the site)
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 100);
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            
            // Images
            $table->string('desktop_image');
            $table->string('mobile_image')->nullable();
            $table->string('alt_text', 255)->nullable();
            
            // Link
            $table->string('link_url')->nullable();
            $table->string('link_text', 100)->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            
            // Placement
            $table->enum('position', [
                'home_hero',
                'home_middle',
                'home_bottom',
                'category_top',
                'product_top',
                'cart_top',
                'checkout_top',
                'sidebar',
                'popup'
            ])->default('home_hero');
            
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            
            // Validity
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            
            // Stats
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['position', 'is_visible', 'display_order']);
            $table->index(['starts_at', 'ends_at']);
        });

        // Static Pages (About, Contact, Terms, Privacy, etc.)
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->longText('content')->nullable();
            $table->string('template', 100)->default('default'); // Page template
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            
            // Status
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            
            // Navigation
            $table->boolean('show_in_footer')->default(false);
            $table->boolean('show_in_header')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            
            // Author
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->foreignId('updated_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_published']);
            $table->index(['show_in_footer', 'display_order']);
            $table->index(['show_in_header', 'display_order']);
        });

        // FAQ
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            
            $table->string('question', 500);
            $table->text('answer');
            $table->string('category', 100)->nullable(); // Ordering, Shipping, Returns, etc.
            
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['category', 'is_visible', 'display_order']);
        });

        // Testimonials / Reviews to show on homepage
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            
            $table->string('customer_name', 200);
            $table->string('customer_title', 100)->nullable(); // "Interior Designer"
            $table->string('customer_image')->nullable();
            $table->string('customer_location', 100)->nullable();
            
            $table->text('content');
            $table->unsignedTinyInteger('rating')->default(5); // 1-5
            
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_featured', 'is_visible', 'display_order']);
        });

        // Site Settings (key-value store for configuration)
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            
            $table->string('key', 100)->unique();
            $table->string('group', 50)->default('general'); // general, contact, social, etc.
            $table->text('value')->nullable();
            $table->enum('type', ['text', 'textarea', 'number', 'boolean', 'json', 'image', 'file'])->default('text');
            
            $table->string('label', 200)->nullable();
            $table->text('description')->nullable();
            
            $table->boolean('is_public')->default(false); // Can be accessed from frontend
            
            $table->timestamps();
            
            $table->index(['group']);
            $table->index(['is_public']);
        });

        // Contact Submissions
        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 200);
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->string('subject', 255)->nullable();
            $table->text('message');
            
            // Status
            $table->enum('status', ['new', 'read', 'replied', 'archived'])->default('new');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            
            // Admin handling
            $table->foreignId('assigned_to_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            
            // Source
            $table->string('ip_address', 45)->nullable();
            $table->string('source_page')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'created_at']);
        });

        // Newsletter Subscribers
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            
            $table->string('email')->unique();
            $table->string('name', 200)->nullable();
            
            $table->enum('status', ['pending', 'subscribed', 'unsubscribed'])->default('pending');
            $table->string('verification_token', 100)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            
            // Source
            $table->string('source', 50)->default('website'); // website, checkout, popup
            $table->string('ip_address', 45)->nullable();
            
            // Preferences
            $table->json('preferences')->nullable(); // What types of emails they want
            
            $table->timestamps();
            
            $table->index(['status']);
            $table->index(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('contact_submissions');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('homepage_sections');
    }
};
