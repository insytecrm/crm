<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lead_scheduled_events', function (Blueprint $table) {
            $table->timestamp('rescheduled_at')->nullable()->after('scheduled_at');
        });

        DB::table('lead_activities')
            ->whereNotNull('metadata')
            ->orderBy('id')
            ->lazy()
            ->each(function (object $activity): void {
                $metadata = json_decode($activity->metadata, true);

                if (! is_array($metadata) || ! ($metadata['rescheduled'] ?? false)) {
                    return;
                }

                $scheduledEventId = $metadata['scheduled_event_id'] ?? null;

                if ($scheduledEventId === null) {
                    return;
                }

                DB::table('lead_scheduled_events')
                    ->where('id', $scheduledEventId)
                    ->whereNull('rescheduled_at')
                    ->update(['rescheduled_at' => $activity->created_at]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_scheduled_events', function (Blueprint $table) {
            $table->dropColumn('rescheduled_at');
        });
    }
};
