<?php

use App\Enums\LeadActivityType;
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
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedInteger('follow_ups_count')->default(0)->after('upcoming_site_visit_at');
            $table->unsignedInteger('site_visits_count')->default(0)->after('follow_ups_count');
        });

        $followUpCounts = DB::table('lead_activities')
            ->where('type', LeadActivityType::FollowUpScheduled->value)
            ->groupBy('lead_id')
            ->selectRaw('lead_id, count(*) as total')
            ->pluck('total', 'lead_id');

        foreach ($followUpCounts as $leadId => $count) {
            DB::table('leads')->where('id', $leadId)->update(['follow_ups_count' => $count]);
        }

        $siteVisitCounts = DB::table('lead_activities')
            ->where('type', LeadActivityType::SiteVisitScheduled->value)
            ->groupBy('lead_id')
            ->selectRaw('lead_id, count(*) as total')
            ->pluck('total', 'lead_id');

        foreach ($siteVisitCounts as $leadId => $count) {
            DB::table('leads')->where('id', $leadId)->update(['site_visits_count' => $count]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['follow_ups_count', 'site_visits_count']);
        });
    }
};
