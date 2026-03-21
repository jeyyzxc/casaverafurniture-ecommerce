<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');

            $table->string('type'); 
            $table->text('data'); 

            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('admin_id')->nullable()->constrained()->onDelete('cascade'); 

            $table->string('title', 255);
            $table->text('message');
            $table->string('type', 50); 
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');

            $table->string('related_type', 100)->nullable(); 
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('action_url')->nullable(); 

            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_dismissed')->default(false);

            $table->string('icon', 50)->nullable();
            $table->string('color', 20)->nullable();
            
            $table->timestamps();
            
            $table->index(['admin_id', 'is_read', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['priority', 'is_read']);
        });

        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            $table->enum('alert_type', ['low_stock', 'out_of_stock', 'back_in_stock'])->default('low_stock');
            $table->unsignedInteger('current_quantity');
            $table->unsignedInteger('threshold_quantity');

            $table->boolean('is_acknowledged')->default(false);
            $table->foreignId('acknowledged_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('acknowledged_at')->nullable();

            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['product_id', 'alert_type']);
            $table->index(['is_acknowledged', 'created_at']);
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();

            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_type', 50)->nullable(); 
            $table->unsignedBigInteger('recipient_id')->nullable();

            $table->string('subject', 255);
            $table->string('template', 100)->nullable();
            $table->longText('body')->nullable();
            $table->json('template_data')->nullable();

            $table->string('type', 50); 

            $table->string('related_type', 100)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();

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

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('stock_alerts');
        Schema::dropIfExists('admin_notifications');
        Schema::dropIfExists('notifications');
    }
};
