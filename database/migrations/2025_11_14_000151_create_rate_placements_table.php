<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_placements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('slot_key')->unique();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('order_column')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for performance
            $table->index('is_active');
            $table->index('order_column');
            $table->index(['is_active', 'order_column']);
        });

        Schema::table('rate_packages', function (Blueprint $table) {
            $table->foreign('rate_placement_id')
                ->references('id')->on('rate_placements')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rate_packages', function (Blueprint $table) {
            $table->dropForeign(['rate_placement_id']);
        });

        Schema::dropIfExists('rate_placements');
    }
};