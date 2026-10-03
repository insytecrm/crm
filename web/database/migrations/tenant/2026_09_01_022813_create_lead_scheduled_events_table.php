<?php

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Support\SchedulingNotes;
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
        Schema::create('lead_scheduled_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->unsignedSmallInteger('sequence_number');
            $table->dateTime('scheduled_at');
            $table->text('notes')->nullable();
            $table->string('status')->default(LeadScheduledEventStatus::Scheduled->value);
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['lead_id', 'type', 'sequence_number']);
            $table->index(['lead_id', 'type', 'status']);
        });

        $this->backfillFromActivities();
    }

    private function backfillFromActivities(): void
    {
        if (! Schema::hasTable('lead_activities')) {
            return;
        }

        foreach ([LeadScheduledEventType::FollowUp, LeadScheduledEventType::SiteVisit] as $type) {
            $scheduledType = $type->scheduledActivityType()->value;
            $completedType = $type->completedActivityType()->value;

            $scheduledActivities = DB::table('lead_activities')
                ->where('type', $scheduledType)
                ->orderBy('created_at')
                ->get();

            $sequenceByLead = [];

            foreach ($scheduledActivities as $activity) {
                $sequenceByLead[$activity->lead_id] = ($sequenceByLead[$activity->lead_id] ?? 0) + 1;

                $completedAt = DB::table('lead_activities')
                    ->where('lead_id', $activity->lead_id)
                    ->where('type', $completedType)
                    ->where('created_at', '>=', $activity->created_at)
                    ->orderBy('created_at')
                    ->value('created_at');

                $nextScheduledAt = DB::table('lead_activities')
                    ->where('lead_id', $activity->lead_id)
                    ->where('type', $scheduledType)
                    ->where('created_at', '>', $activity->created_at)
                    ->orderBy('created_at')
                    ->value('created_at');

                $isCompleted = $completedAt !== null
                    && ($nextScheduledAt === null || $completedAt < $nextScheduledAt);

                DB::table('lead_scheduled_events')->insert([
                    'lead_id' => $activity->lead_id,
                    'type' => $type->value,
                    'sequence_number' => $sequenceByLead[$activity->lead_id],
                    'scheduled_at' => $this->scheduledAtFromActivity($activity, $type),
                    'notes' => SchedulingNotes::fromDescription($activity->description),
                    'status' => $isCompleted ? LeadScheduledEventStatus::Completed->value : LeadScheduledEventStatus::Scheduled->value,
                    'completed_at' => $isCompleted ? $completedAt : null,
                    'user_id' => $activity->user_id,
                    'created_at' => $activity->created_at,
                ]);
            }
        }
    }

    private function scheduledAtFromActivity(object $activity, LeadScheduledEventType $type): string
    {
        $leadColumn = $type === LeadScheduledEventType::FollowUp
            ? 'next_follow_up_at'
            : 'upcoming_site_visit_at';

        $leadValue = DB::table('leads')->where('id', $activity->lead_id)->value($leadColumn);

        return $leadValue ?? $activity->created_at;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_scheduled_events');
    }
};
