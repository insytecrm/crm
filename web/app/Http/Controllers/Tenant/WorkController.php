<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Queries\WorkQueue;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkController extends Controller
{
    public function __invoke(Request $request, WorkQueue $workQueue): View
    {
        $filter = $request->string('filter')->toString();

        return view('tenant.work.index', [
            'items' => $workQueue->itemsFor($request->user(), $filter !== '' ? $filter : null),
            'filter' => $filter !== '' ? $filter : 'all',
            'managerTriage' => $workQueue->managerTriageFor($request->user()),
            'myDayActivities' => collect(),
        ]);
    }
}
