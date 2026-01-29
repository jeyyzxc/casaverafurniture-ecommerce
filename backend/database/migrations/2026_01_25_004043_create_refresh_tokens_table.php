<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing incomplete table if it exists
        Schema::dropIfExists('refresh_tokens');

        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable'); // Supports both User and Admin models (creates index automatically)
            $table->string('token_hash', 64)->unique(); // Only store hash, not plain token (creates unique index)
            $table->timestamp('expires_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Additional indexes for performance (morphs() already creates tokenable_type/tokenable_id index)
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
