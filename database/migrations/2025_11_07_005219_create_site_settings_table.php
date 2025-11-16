<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, image, json, etc
            $table->text('description')->nullable();
            $table->string('group')->default('general'); // general, contact, social, appearance, etc
            $table->timestamps();

            // Indexes for performance
            $table->index('group');
            $table->index(['group', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};