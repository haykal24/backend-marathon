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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('category')->default('general')->index(); // marathon, pace, fun_run, trail_run, etc
            $table->string('keyword')->nullable()->index(); // Reference to SEO keyword
            $table->string('question'); // Question text
            $table->longText('answer'); // Answer text (supports HTML/markdown)
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->integer('views')->default(0);
            $table->string('related_keyword')->nullable(); // Related SEO keyword for internal linking
            $table->timestamps();

            // Indexes for performance
            $table->index(['is_active', 'category']);
            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
