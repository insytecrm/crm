<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\LeadScheduledEventType;
use App\Enums\ScheduledActivityStage;
use App\Http\Controllers\Controller;
use App\Queries\ScheduledActivities;
use App\Support\DataTable\DataTableViewData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteVisitController extends Controller
{
    public function index(Request $request, ScheduledActivities $scheduledActivities): View
    {
        $stage = ScheduledActivityStage::fromRequest($request->string('stage')->toString());
        $search = $request->string('search')->trim()->toString();

        $activities = $scheduledActivities->paginateByTypeAndStage(
            LeadScheduledEventType::SiteVisit,
            $stage,
            $search,
        );

        return view('tenant.activities.site-visits.index', array_merge([
            'stage' => $stage,
            'stages' => ScheduledActivityStage::tabs(),
            'statistics' => $scheduledActivities->stageStatistics(LeadScheduledEventType::SiteVisit),
            'activities' => $activities,
            'search' => $search,
        ], DataTableViewData::for($request->user(), 'site_visits', $activities, 'event_id', $stage->value)));
    }
}
