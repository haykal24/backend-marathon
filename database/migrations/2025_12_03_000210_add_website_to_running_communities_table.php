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
        Schema::table('running_communities', function (Blueprint $table) {
            if (!Schema::hasColumn('running_communities', 'website')) {
                $table->string('website', 255)->nullable()->after('instagram_handle');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('running_communities', function (Blueprint $table) {
            if (Schema::hasColumn('running_communities', 'website')) {
                $table->dropColumn('website');
            }
        });
    }
};


