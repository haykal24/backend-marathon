<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured_frontend')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'is_featured_frontend']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
