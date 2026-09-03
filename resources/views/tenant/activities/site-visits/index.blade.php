@include('tenant.activities.partials.scheduled-activity-index', [
    'title' => __('Site Visits'),
    'description' => __('Track upcoming, overdue, and complete site visits across your leads'),
    'stage' => $stage,
    'stages' => $stages,
    'statistics' => $statistics,
    'activities' => $activities,
    'search' => $search,
    'indexRoute' => 'tenant.site-visits.index',
    'activityLabel' => __('Site Visits'),
])
