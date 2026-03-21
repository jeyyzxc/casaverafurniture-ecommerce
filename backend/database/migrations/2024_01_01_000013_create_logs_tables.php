<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->string('causer_type', 100)->nullable(); 
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->string('causer_name', 200)->nullable(); 

            $table->string('subject_type', 100)->nullable(); 
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_name', 255)->nullable(); 

            $table->string('action', 50); 
            $table->string('module', 50)->nullable(); 
            $table->text('description')->nullable();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('properties')->nullable(); 

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable(); 
            
            $table->timestamps();

            $table->index(['causer_type', 'causer_id']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['action', 'created_at']);
            $table->index(['module', 'created_at']);
            $table->index(['created_at']);
        });

        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->integer('quantity_change'); 
            $table->unsignedInteger('quantity_before');
            $table->unsignedInteger('quantity_after');

            $table->enum('type', [
                'initial',          
                'purchase',         
                'sale',             
                'return',           
                'adjustment',       
                'damage',           
                'theft',            
                'transfer_in',      
                'transfer_out',     
                'reserved',         
                'unreserved',       
                'count_correction'  
            ])->default('adjustment');

            $table->string('reference_type', 100)->nullable(); 
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number', 100)->nullable(); 

            $table->text('notes')->nullable();

            $table->foreignId('admin_id')->nullable()->constrained()->onDelete('set null');
            $table->string('admin_name', 200)->nullable(); 

            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['admin_id']);
        });

        Schema::create('order_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            $table->string('action', 100); 
            $table->text('description');

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->string('performed_by_type', 50)->nullable(); 
            $table->unsignedBigInteger('performed_by_id')->nullable();
            $table->string('performed_by_name', 200)->nullable();

            $table->boolean('customer_notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['order_id', 'created_at']);
            $table->index(['action']);
        });

        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            $table->string('action', 100); 
            $table->text('description')->nullable();
            
            $table->json('request_data')->nullable(); 
            $table->json('response_data')->nullable(); 
            
            $table->string('performed_by_type', 50)->nullable();
            $table->unsignedBigInteger('performed_by_id')->nullable();
            
            $table->string('ip_address', 45)->nullable();
            
            $table->timestamps();
            
            $table->index(['payment_id', 'created_at']);
            $table->index(['order_id']);
            $table->index(['action']);
        });

        Schema::create('admin_login_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('admin_id')->nullable()->constrained()->onDelete('set null');
            $table->string('email'); 
            
            $table->enum('status', ['success', 'failed', 'blocked'])->default('success');
            $table->string('failure_reason', 100)->nullable();
            
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('location', 200)->nullable(); 
            
            $table->timestamps();
            
            $table->index(['admin_id', 'created_at']);
            $table->index(['email', 'status']);
            $table->index(['ip_address']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('user_login_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('email');
            
            $table->enum('status', ['success', 'failed', 'blocked'])->default('success');
            $table->string('failure_reason', 100)->nullable();
            
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['email', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_login_logs');
        Schema::dropIfExists('admin_login_logs');
        Schema::dropIfExists('payment_logs');
        Schema::dropIfExists('order_logs');
        Schema::dropIfExists('stock_logs');
        Schema::dropIfExists('activity_logs');
    }
};
