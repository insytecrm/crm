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
            $table->index('created_at');
            $table->index('lead_score');
        });

        Schema::table('lead_scheduled_events', function (Blueprint $table) {
            $table->index('scheduled_at');
            $table->index(['type', 'status']);
        });

        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->index('status');
            $table->index('due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['lead_score']);
        });

        Schema::table('lead_scheduled_events', function (Blueprint $table) {
            $table->dropIndex(['scheduled_at']);
            $table->dropIndex(['type', 'status']);
        });

        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['due_at']);
        });
    }
};
