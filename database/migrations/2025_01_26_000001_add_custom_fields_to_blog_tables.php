<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     * Menambahkan field untuk custom blog system tanpa mengubah struktur yang sudah ada
     */
    public function up(): void
    {
        // Tambahkan field untuk toggle frontend di blog_posts
        Schema::table('blog_posts', function (Blueprint $table) {
            // Toggle untuk menentukan artikel muncul di frontend mana
            // null = kedua portal (default), true = hanya maraton.id, false = hanya indonesiamarathon.com
            $table->boolean('is_for_maraton_id')->nullable()->default(null)->after('published_at');
            
            // SEO fields untuk Google Article schema
            $table->string('seo_title')->nullable()->after('title');
            $table->text('seo_description')->nullable()->after('seo_title');
            
            // Status untuk draft/published
            $table->string('status')->default('draft')->after('published_at');
            
            // Featured untuk highlight artikel
            $table->boolean('is_featured')->default(false)->after('status');
            
            // View count untuk analytics
            $table->unsignedBigInteger('view_count')->default(0)->after('is_featured');
        });

        // Tambahkan field SEO di blog_categories
        Schema::table('blog_categories', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('description');
            $table->text('seo_description')->nullable()->after('seo_title');
        });

        // Tambahkan field untuk author (jika belum ada)
        Schema::table('blog_authors', function (Blueprint $table) {
            // Field photo sudah ada, tapi kita akan pakai Spatie Media Library juga
            // Jadi field photo tetap untuk backward compatibility
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn([
                'is_for_maraton_id',
                'seo_title',
                'seo_description',
                'status',
                'is_featured',
                'view_count',
            ]);
        });

        Schema::table('blog_categories', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description']);
        });
    }
};