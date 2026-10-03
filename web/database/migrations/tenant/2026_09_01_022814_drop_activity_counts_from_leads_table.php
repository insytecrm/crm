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
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['follow_ups_count', 'site_visits_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedInteger('follow_ups_count')->default(0)->after('upcoming_site_visit_at');
            $table->unsignedInteger('site_visits_count')->default(0)->after('follow_ups_count');
        });
    }
};
