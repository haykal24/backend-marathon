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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('location_name');
            $table->string('city', 100);
            $table->string('province')->nullable();
            $table->date('event_date');
            $table->date('event_end_date')->nullable();
            $table->string('event_type', 100);
            $table->string('registration_url', 2048)->nullable();
            $table->string('organizer_name')->nullable();
            $table->json('benefits')->nullable();
            $table->json('contact_info')->nullable();
            $table->json('registration_fees')->nullable();
            $table->json('social_media')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('status', 50)->default('pending_review');
            $table->boolean('is_featured_hero')->default(false)->comment('Tampil di hero slider homepage');
            $table->timestamp('featured_hero_expires_at')->nullable()->comment('Tanggal kedaluwarsa event di hero slider');
            $table->timestamps();

            // Indexes for faster querying & filtering
            $table->index(['status', 'event_date']);
            $table->index('event_type');
            $table->index('city');
            $table->index('province');
            $table->index('is_featured_hero');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
