<?php

namespace App\Support;

use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\SiteVisitType;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use Illuminate\Support\Collection;

class LeadHistorySummary
{
    /**
     * @return array{
     *     insight: string,
     *     follow_ups_completed: int,
     *     site_visits_completed: int,
     *     projects: list<array{
     *         label: string,
     *         fresh_visits: int,
     *         revisits: int,
     *         total_visits: int
     *     }>
     * }
     */
    public function for(Lead $lead): array
    {
        $events = $this->events($lead);
        $completedFollowUps = $events
            ->where('type', LeadScheduledEventType::FollowUp)
            ->where('status', LeadScheduledEventStatus::Completed)
            ->count();
        $completedSiteVisits = $events
            ->where('type', LeadScheduledEventType::SiteVisit)
            ->where('status', LeadScheduledEventStatus::Completed)
            ->whereNotNull('property_id');
        $projects = $this->projectsVisited($completedSiteVisits);

        return [
            'insight' => $this->insight($lead, $completedFollowUps, $projects),
            'follow_ups_completed' => $completedFollowUps,
            'site_visits_completed' => $completedSiteVisits->count(),
            'projects' => $projects,
        ];
    }

    /**
     * @return Collection<int, LeadScheduledEvent>
     */
    private function events(Lead $lead): Collection
    {
        if ($lead->relationLoaded('scheduledEvents')) {
            return $lead->scheduledEvents;
        }

        return $lead->scheduledEvents()->with('property')->get();
    }

    /**
     * @param  Collection<int, LeadScheduledEvent>  $completedSiteVisits
     * @return list<array{
     *     label: string,
     *     fresh_visits: int,
     *     revisits: int,
     *     total_visits: int
     * }>
     */
    private function projectsVisited(Collection $completedSiteVisits): array
    {
        return $completedSiteVisits
            ->groupBy('property_id')
            ->map(function (Collection $visits): ?array {
                $property = $visits->first()?->property;

                if ($property === null) {
                    return null;
                }

                $freshVisits = $visits->where('visit_type', SiteVisitType::FreshVisit)->count();
                $revisits = $visits->where('visit_type', SiteVisitType::Revisit)->count();

                return [
                    'label' => $property->listLabel(),
                    'fresh_visits' => $freshVisits,
                    'revisits' => $revisits,
                    'total_visits' => $visits->count(),
                ];
            })
            ->filter()
            ->sortBy('label')
            ->values()
            ->all();
    }

    /**
     * @param  list<array{label: string, fresh_visits: int, revisits: int, total_visits: int}>  $projects
     */
    private function insight(Lead $lead, int $completedFollowUps, array $projects): string
    {
        $sentences = [];

        $intro = __(':name is currently in :status', [
            'name' => $lead->name,
            'status' => $lead->status->label(),
        ]);

        if (filled($lead->source)) {
            $intro = __(':name was added from :source and is currently in :status', [
                'name' => $lead->name,
                'source' => $lead->source,
                'status' => $lead->status->label(),
            ]);
        }

        $sentences[] = $intro.'.';

        $sentences[] = trans_choice(
            ':count follow-up has been completed.|:count follow-ups have been completed.',
            $completedFollowUps,
            ['count' => $completedFollowUps]
        );

        if ($projects === []) {
            $sentences[] = __('No site visits have been completed yet.');
        } else {
            $projectParts = collect($projects)->map(function (array $project): string {
                $parts = [];

                if ($project['fresh_visits'] > 0) {
                    $parts[] = trans_choice(
                        ':count Fresh Visit|:count Fresh Visits',
                        $project['fresh_visits'],
                        ['count' => $project['fresh_visits']]
                    );
                }

                if ($project['revisits'] > 0) {
                    $parts[] = trans_choice(
                        ':count Revisit|:count Revisits',
                        $project['revisits'],
                        ['count' => $project['revisits']]
                    );
                }

                if ($parts === []) {
                    $parts[] = trans_choice(
                        ':count visit|:count visits',
                        $project['total_visits'],
                        ['count' => $project['total_visits']]
                    );
                }

                return __(':project (:details)', [
                    'project' => $project['label'],
                    'details' => implode(', ', $parts),
                ]);
            })->implode('; ');

            $sentences[] = __('Visited :projects.', ['projects' => $projectParts]);
        }

        return implode(' ', $sentences);
    }
}
