<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->text('cancellation_notes')->nullable()->after('completion_notes');
        });
    }

    public function down(): void
    {
        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->dropColumn('cancellation_notes');
        });
    }
};
