<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_category_id')->nullable()->index();
            $table->foreignId('rate_placement_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('placement_key')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('price_display')->nullable();
            $table->string('duration')->nullable();
            $table->string('audience')->nullable();
            $table->json('deliverables')->nullable();
            $table->json('channels')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order_column')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('order_column');
            $table->index(['rate_category_id', 'is_active']);
            $table->index(['rate_placement_id', 'is_active']);
            $table->index(['is_active', 'order_column']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_packages');
    }
};
