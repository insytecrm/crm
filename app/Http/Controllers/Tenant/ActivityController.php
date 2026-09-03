<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\ActivityFilter;
use App\Enums\ActivityKind;
use App\Http\Controllers\Controller;
use App\Queries\ScheduledActivities;
use App\Support\DataTable\DataTableViewData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request, ScheduledActivities $scheduledActivities): View
    {
        $filter = ActivityFilter::fromRequest($request->string('filter')->toString());
        $kind = ActivityKind::fromRequest($request->string('kind')->toString());

        $activities = $scheduledActivities->items($filter, $kind);
        $tableKey = $kind === ActivityKind::SiteVisit ? 'activities_site_visits' : 'activities';

        return view('tenant.activities.index', array_merge([
            'filter' => $filter,
            'kind' => $kind,
            'statistics' => $scheduledActivities->statistics($filter),
            'activities' => $activities,
            'showPropertyColumn' => $kind === ActivityKind::SiteVisit,
        ], DataTableViewData::for($request->user(), $tableKey, $activities, 'event_id', $filter->value)));
    }
}
