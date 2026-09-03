@include('tenant.activities.partials.scheduled-activity-index', [
    'title' => __('Follow-Ups'),
    'description' => __('Track upcoming, overdue, and complete follow-ups across your leads'),
    'stage' => $stage,
    'stages' => $stages,
    'statistics' => $statistics,
    'activities' => $activities,
    'search' => $search,
    'indexRoute' => 'tenant.follow-ups.index',
    'activityLabel' => __('Follow-Ups'),
    'isFollowUpList' => true,
])
