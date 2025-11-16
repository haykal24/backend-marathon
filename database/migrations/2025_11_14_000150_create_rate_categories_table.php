<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('order_column')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('default_group')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('is_active');
            $table->index('order_column');
            $table->index(['is_active', 'order_column']);
        });

        Schema::table('rate_packages', function (Blueprint $table) {
            $table->foreign('rate_category_id')
                ->references('id')->on('rate_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rate_packages', function (Blueprint $table) {
            $table->dropForeign(['rate_category_id']);
        });

        Schema::dropIfExists('rate_categories');
    }
};
