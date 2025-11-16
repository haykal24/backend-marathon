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
        Schema::create('ad_banners', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('target_url', 2048)->nullable();
            $table->string('slot_location', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['slot_location', 'is_active', 'expires_at']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_banners');
    }
};
