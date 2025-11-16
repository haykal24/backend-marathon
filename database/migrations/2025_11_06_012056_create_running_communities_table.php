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
        Schema::create('running_communities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('location')->nullable();
            $table->string('instagram_handle', 100)->nullable();
            $table->string('contact_info')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('running_communities');
    }
};
