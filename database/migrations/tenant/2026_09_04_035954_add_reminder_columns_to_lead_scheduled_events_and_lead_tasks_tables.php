<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_scheduled_events', function (Blueprint $table) {
            $table->timestamp('remind_at')->nullable()->after('scheduled_at');
            $table->unsignedInteger('reminder_before_seconds')->nullable()->after('remind_at');
            $table->timestamp('reminder_dismissed_at')->nullable()->after('reminder_before_seconds');
            $table->index(['remind_at', 'reminder_dismissed_at']);
        });

        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->timestamp('remind_at')->nullable()->after('due_at');
            $table->unsignedInteger('reminder_before_seconds')->nullable()->after('remind_at');
            $table->timestamp('reminder_dismissed_at')->nullable()->after('reminder_before_seconds');
            $table->index(['remind_at', 'reminder_dismissed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('lead_scheduled_events', function (Blueprint $table) {
            $table->dropIndex(['remind_at', 'reminder_dismissed_at']);
            $table->dropColumn(['remind_at', 'reminder_before_seconds', 'reminder_dismissed_at']);
        });

        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->dropIndex(['remind_at', 'reminder_dismissed_at']);
            $table->dropColumn(['remind_at', 'reminder_before_seconds', 'reminder_dismissed_at']);
        });
    }
};
