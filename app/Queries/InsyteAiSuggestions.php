<?php

namespace App\Queries;

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use App\Models\User;

class InsyteAiSuggestions
{
    public function __construct(
        private DashboardTodaysTasks $dashboardTodaysTasks,
    ) {}

    /**
     * @return list<array{label: string, href: string}>
     */
    public function forUser(User $user): array
    {
        $suggestions = [];

        $overdueFollowUps = LeadScheduledEvent::query()
            ->where('type', LeadScheduledEventType::FollowUp)
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->where('scheduled_at', '<', now())
            ->whereHas('lead')
            ->count();

        if ($overdueFollowUps > 0) {
            $suggestions[] = [
                'label' => trans_choice(':count lead needs immediate follow-up|:count leads need immediate follow-up', $overdueFollowUps, ['count' => $overdueFollowUps]),
                'href' => route('tenant.follow-ups.index'),
            ];
        }

        $todaySiteVisits = LeadScheduledEvent::query()
            ->where('type', LeadScheduledEventType::SiteVisit)
            ->where('status', LeadScheduledEventStatus::Scheduled)
            ->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()])
            ->whereHas('lead')
            ->count();

        if ($todaySiteVisits > 0) {
            $suggestions[] = [
                'label' => trans_choice(':count site visit is scheduled today|:count site visits are scheduled today', $todaySiteVisits, ['count' => $todaySiteVisits]),
                'href' => route('tenant.site-visits.index'),
            ];
        }

        $tasksDue = $this->dashboardTodaysTasks->forTenant()->count();

        if ($tasksDue > 0) {
            $suggestions[] = [
                'label' => trans_choice(':count task is due today|:count tasks are due today', $tasksDue, ['count' => $tasksDue]),
                'href' => route('tenant.tasks.index'),
            ];
        }

        $priorityLeads = Lead::query()->priority()->count();

        if ($priorityLeads > 0) {
            $suggestions[] = [
                'label' => trans_choice(':count priority lead needs attention|:count priority leads need attention', $priorityLeads, ['count' => $priorityLeads]),
                'href' => route('tenant.leads.priority.index'),
            ];
        }

        if ($suggestions === []) {
            $suggestions[] = [
                'label' => __('No urgent items right now. Ask Chat to find a lead or schedule a follow-up.'),
                'href' => route('tenant.ai.index'),
            ];
        }

        return array_slice($suggestions, 0, 4);
    }
}
