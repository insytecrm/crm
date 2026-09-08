<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('google_sheet_connections', function (Blueprint $table) {
            $table->unsignedInteger('total_synced')->default(0)->after('last_synced_at');
            $table->unsignedInteger('total_skipped')->default(0)->after('total_synced');
            $table->unsignedInteger('total_failed')->default(0)->after('total_skipped');
        });
    }

    public function down(): void
    {
        Schema::table('google_sheet_connections', function (Blueprint $table) {
            $table->dropColumn(['total_synced', 'total_skipped', 'total_failed']);
        });
    }
};
