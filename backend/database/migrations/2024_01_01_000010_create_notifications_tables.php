<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * NOTIFICATIONS TABLES
     * Real-time notifications for admin and clients
     */
    public function up(): void
    {
        // User Notifications (for clients)
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Polymorphic relation (can notify users or admins)
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            
            // Notification content
            $table->string('type'); // Class name of notification
            $table->text('data'); // JSON data
            
            // Read status
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        // Admin Notifications (specifically for admin panel)
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            
            // Target admin(s)
            $table->foreignId('admin_id')->nullable()->constrained()->onDelete('cascade'); // null = all admins
            
            // Notification details
            $table->string('title', 255);
            $table->text('message');
            $table->string('type', 50); // order, payment, stock, user, system
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Related entity
            $table->string('related_type', 100)->nullable(); // order, payment, product, user
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('action_url')->nullable(); // Link to relevant page
            
            // Status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_dismissed')->default(false);
            
            // Icon/styling
            $table->string('icon', 50)->nullable();
            $table->string('color', 20)->nullable();
            
            $table->timestamps();
            
            $table->index(['admin_id', 'is_read', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['priority', 'is_read']);
        });

        // Stock Alerts (low stock notifications)
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            $table->enum('alert_type', ['low_stock', 'out_of_stock', 'back_in_stock'])->default('low_stock');
            $table->unsignedInteger('current_quantity');
            $table->unsignedInteger('threshold_quantity');
            
            // Alert status
            $table->boolean('is_acknowledged')->default(false);
            $table->foreignId('acknowledged_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('acknowledged_at')->nullable();
            
            // Notification sent
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['product_id', 'alert_type']);
            $table->index(['is_acknowledged', 'created_at']);
        });

        // Email Queue / Email Logs
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            
            // Recipient
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_type', 50)->nullable(); // user, admin, guest
            $table->unsignedBigInteger('recipient_id')->nullable();
            
            // Email content
            $table->string('subject', 255);
            $table->string('template', 100)->nullable();
            $table->longText('body')->nullable();
            $table->json('template_data')->nullable();
            
            // Email type/category
            $table->string('type', 50); // order_confirmation, shipping, newsletter, etc.
            
            // Related entity
            $table->string('related_type', 100)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            
            $table->timestamps();
            
            $table->index(['recipient_email', 'created_at']);
            $table->index(['type', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('stock_alerts');
        Schema::dropIfExists('admin_notifications');
        Schema::dropIfExists('notifications');
    }
};
