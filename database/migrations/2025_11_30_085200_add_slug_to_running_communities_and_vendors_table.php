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
        // Add slug to running_communities
        Schema::table('running_communities', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('name');
            $table->string('city')->nullable()->after('location');
        });

        // Add slug to vendors
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('name');
        });

        // Generate slugs for existing records
        $this->generateSlugsForExistingRecords();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('running_communities', function (Blueprint $table) {
            $table->dropColumn(['slug', 'city']);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }

    /**
     * Generate slugs for existing records
     */
    private function generateSlugsForExistingRecords(): void
    {
        // Generate slugs for running communities
        \App\Models\RunningCommunity::whereNull('slug')->chunk(100, function ($communities) {
            foreach ($communities as $community) {
                $baseSlug = \Illuminate\Support\Str::slug($community->name);
                $slug = $baseSlug;
                $counter = 1;
                
                while (\App\Models\RunningCommunity::where('slug', $slug)->where('id', '!=', $community->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $community->update(['slug' => $slug]);
            }
        });

        // Generate slugs for vendors
        \App\Models\Vendor::whereNull('slug')->chunk(100, function ($vendors) {
            foreach ($vendors as $vendor) {
                $baseSlug = \Illuminate\Support\Str::slug($vendor->name);
                $slug = $baseSlug;
                $counter = 1;
                
                while (\App\Models\Vendor::where('slug', $slug)->where('id', '!=', $vendor->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $vendor->update(['slug' => $slug]);
            }
        });
    }
};
