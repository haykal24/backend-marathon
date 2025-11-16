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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 100);
            $table->text('description')->nullable();
            $table->string('city')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            // Indexes for frequently queried attributes
            $table->index('type');
            $table->index('city');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
