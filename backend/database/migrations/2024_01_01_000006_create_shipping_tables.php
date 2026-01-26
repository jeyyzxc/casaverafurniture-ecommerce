<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * SHIPPING CONFIGURATION TABLES
     * Shipping zones, rates, and courier integrations
     */
    public function up(): void
    {
        // Shipping Zones Table
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 100);
            $table->enum('type', ['local', 'national', 'international'])->default('local');
            $table->text('description')->nullable();
            
            // Zone coverage (regions/provinces covered)
            $table->json('regions')->nullable(); // Array of provinces/cities
            $table->json('postal_codes')->nullable(); // Array or range of postal codes
            
            // Default rates
            $table->decimal('base_rate', 10, 2)->default(0);
            $table->decimal('free_shipping_threshold', 10, 2)->nullable(); // Free if order > this amount
            
            // Delivery estimates
            $table->unsignedInteger('min_delivery_days')->default(1);
            $table->unsignedInteger('max_delivery_days')->default(5);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_active', 'display_order']);
            $table->index(['type']);
        });

        // Shipping Rates Table (different rates within a zone)
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->onDelete('cascade');
            
            $table->string('name', 100); // "Standard", "Express", "Same Day"
            $table->text('description')->nullable();
            
            // Rate calculation
            $table->enum('rate_type', ['flat', 'weight_based', 'price_based', 'item_based'])->default('flat');
            $table->decimal('rate', 10, 2); // Base rate
            $table->decimal('per_kg_rate', 10, 2)->nullable(); // Additional per kg
            $table->decimal('per_item_rate', 10, 2)->nullable(); // Additional per item
            
            // Weight limits
            $table->decimal('min_weight', 8, 2)->nullable();
            $table->decimal('max_weight', 8, 2)->nullable();
            
            // Order amount limits
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->decimal('max_order_amount', 10, 2)->nullable();
            
            // Delivery estimates for this specific rate
            $table->unsignedInteger('min_delivery_days')->default(1);
            $table->unsignedInteger('max_delivery_days')->default(5);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            
            $table->timestamps();
            
            $table->index(['shipping_zone_id', 'is_active']);
        });

        // Couriers/Carriers Table
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 100);
            $table->string('code', 50)->unique(); // "lbc", "jt", "grab", etc.
            $table->string('logo')->nullable();
            $table->string('tracking_url')->nullable(); // URL template for tracking
            $table->string('website')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('contact_email')->nullable();
            
            // API Integration (if any)
            $table->boolean('has_api_integration')->default(false);
            $table->json('api_config')->nullable(); // API credentials, endpoints
            
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Courier-Zone availability
        Schema::create('courier_shipping_zone', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipping_zone_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['courier_id', 'shipping_zone_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_shipping_zone');
        Schema::dropIfExists('couriers');
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_zones');
    }
};
