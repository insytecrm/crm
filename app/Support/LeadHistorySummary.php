<?php

namespace App\Support;

use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\SiteVisitType;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadScheduledEvent;
use Illuminate\Support\Carbon;
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
     *     }>,
     *     journey: list<array{
     *         key: string,
     *         label: string,
     *         detail: ?string,
     *         occurred_at: ?Carbon,
     *         state: string
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
        $booking = $this->booking($lead);

        return [
            'insight' => $this->insight($lead, $completedFollowUps, $projects, $booking),
            'follow_ups_completed' => $completedFollowUps,
            'site_visits_completed' => $completedSiteVisits->count(),
            'projects' => $projects,
            'journey' => $this->journey($lead, $completedFollowUps, $completedSiteVisits, $booking),
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

    private function booking(Lead $lead): ?Booking
    {
        if ($lead->relationLoaded('latestBooking')) {
            return $lead->latestBooking;
        }

        return $lead->latestBooking()->with('property')->first();
    }

    /**
     * @return Collection<int, LeadActivity>
     */
    private function activities(Lead $lead): Collection
    {
        if ($lead->relationLoaded('activities')) {
            return $lead->activities;
        }

        return $lead->activities()->get();
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
     * @param  Collection<int, LeadScheduledEvent>  $completedSiteVisits
     * @return list<array{
     *     key: string,
     *     label: string,
     *     detail: ?string,
     *     occurred_at: ?Carbon,
     *     state: string
     * }>
     */
    private function journey(Lead $lead, int $completedFollowUps, Collection $completedSiteVisits, ?Booking $booking): array
    {
        $progress = $this->progressRank($lead, $booking);
        $lost = $lead->status === LeadStatus::Lost;
        $skipIncompletePipeline = $lost;
        $bookingDetail = $this->bookingDetail($booking);

        $lastFollowUpAt = $this->events($lead)
            ->where('type', LeadScheduledEventType::FollowUp)
            ->where('status', LeadScheduledEventStatus::Completed)
            ->sortByDesc('completed_at')
            ->first()?->completed_at;

        $lastSiteVisitAt = $completedSiteVisits
            ->sortByDesc('completed_at')
            ->first()?->completed_at;

        $draft = [
            $this->draftStep('created', __('Lead created'), null, $lead->created_at, true, false),
            $this->draftStep(
                'contacted',
                __('Contacted'),
                null,
                $this->statusReachedAt($lead, LeadStatus::Contacted),
                $progress >= 1,
                $skipIncompletePipeline && $progress < 1,
            ),
            $this->draftStep(
                'qualified',
                __('Qualified'),
                null,
                $this->statusReachedAt($lead, LeadStatus::Qualified),
                $progress >= 2,
                $skipIncompletePipeline && $progress < 2,
            ),
            $this->draftStep(
                'follow_up',
                __('Follow-up'),
                $completedFollowUps > 0
                    ? __('Completed: :count', ['count' => $completedFollowUps])
                    : null,
                $lastFollowUpAt ?? $this->statusReachedAt($lead, LeadStatus::FollowUp),
                $progress >= 3 || $completedFollowUps > 0,
                $skipIncompletePipeline && $progress < 3 && $completedFollowUps === 0,
            ),
            $this->draftStep(
                'site_visit',
                __('Site visit'),
                $completedSiteVisits->isNotEmpty()
                    ? __('Completed: :count', ['count' => $completedSiteVisits->count()])
                    : null,
                $lastSiteVisitAt ?? $this->statusReachedAt($lead, LeadStatus::SiteVisit),
                $progress >= 4 || $completedSiteVisits->isNotEmpty(),
                $skipIncompletePipeline && $progress < 4 && $completedSiteVisits->isEmpty(),
            ),
            $this->draftStep(
                'negotiation',
                __('Negotiation'),
                null,
                $this->statusReachedAt($lead, LeadStatus::Negotiation),
                $progress >= 5,
                $skipIncompletePipeline && $progress < 5,
            ),
        ];

        if ($lost) {
            $draft[] = $this->draftStep(
                'lost',
                __('Lost'),
                $lead->closing_reason?->label(),
                $this->statusReachedAt($lead, LeadStatus::Lost) ?? $lead->closed_at,
                true,
                false,
            );
        }

        if ($booking !== null || ! $lost) {
            $draft[] = $this->draftStep(
                'booking',
                __('Booking'),
                $bookingDetail,
                $booking?->booking_date,
                $booking !== null,
                $lost && $booking === null,
            );
            $draft[] = $this->draftStep(
                'agreement',
                __('Agreement'),
                null,
                $booking?->agreement_date,
                $booking?->hasAgreement() ?? false,
                $lost && $booking === null,
            );
            $draft[] = $this->draftStep(
                'invoice',
                __('Invoice created'),
                $booking?->invoice_number,
                $booking?->invoiced_at ?? $booking?->invoice_date,
                $booking?->hasInvoice() ?? false,
                $lost && $booking === null,
            );
            $draft[] = $this->draftStep(
                'payout',
                __('Payout received'),
                null,
                $booking?->payout_paid_at,
                $booking?->hasPaidPayout() ?? false,
                $lost && $booking === null,
            );
        }

        return $this->assignJourneyStates($draft);
    }

    /**
     * @return array{key: string, label: string, detail: ?string, occurred_at: ?Carbon, completed: bool, skipped: bool}
     */
    private function draftStep(string $key, string $label, ?string $detail, mixed $occurredAt, bool $completed, bool $skipped): array
    {
        $occurredAt = $occurredAt === null ? null : Carbon::parse($occurredAt);

        return [
            'key' => $key,
            'label' => $label,
            'detail' => $detail,
            'occurred_at' => $occurredAt,
            'completed' => $completed,
            'skipped' => $skipped,
        ];
    }

    /**
     * @param  list<array{key: string, label: string, detail: ?string, occurred_at: ?Carbon, completed: bool, skipped: bool}>  $draft
     * @return list<array{key: string, label: string, detail: ?string, occurred_at: ?Carbon, state: string}>
     */
    private function assignJourneyStates(array $draft): array
    {
        $currentAssigned = false;
        $journey = [];

        foreach ($draft as $step) {
            $state = 'upcoming';

            if ($step['completed']) {
                $state = 'completed';
            } elseif ($step['skipped']) {
                $state = 'skipped';
            } elseif (! $currentAssigned) {
                $state = 'current';
                $currentAssigned = true;
            }

            $journey[] = [
                'key' => $step['key'],
                'label' => $step['label'],
                'detail' => $step['detail'],
                'occurred_at' => $step['occurred_at'],
                'state' => $state,
            ];
        }

        return $journey;
    }

    private function bookingDetail(?Booking $booking): ?string
    {
        if ($booking === null) {
            return null;
        }

        $property = $booking->property?->project_name ?? __('Unknown Project');

        return __(':property — Unit :unit', [
            'property' => $property,
            'unit' => $booking->unit_number,
        ]);
    }

    private function progressRank(Lead $lead, ?Booking $booking): int
    {
        if ($lead->status !== LeadStatus::Lost) {
            return $this->statusRank($lead->status);
        }

        $max = $this->activities($lead)
            ->where('type', LeadActivityType::StatusChanged)
            ->map(function (LeadActivity $activity): int {
                $to = $activity->metadata['to'] ?? null;

                if (! is_string($to) || $to === LeadStatus::Lost->value) {
                    return 0;
                }

                $status = LeadStatus::tryFrom($to);

                return $status instanceof LeadStatus ? $this->statusRank($status) : 0;
            })
            ->max();

        $rank = (int) $max;

        if ($booking !== null) {
            $rank = max($rank, 6);
        }

        return $rank;
    }

    private function statusRank(LeadStatus $status): int
    {
        return match ($status) {
            LeadStatus::New => 0,
            LeadStatus::Contacted => 1,
            LeadStatus::Qualified => 2,
            LeadStatus::FollowUp => 3,
            LeadStatus::SiteVisit => 4,
            LeadStatus::Negotiation => 5,
            LeadStatus::Converted => 6,
            LeadStatus::Lost => 0,
        };
    }

    private function statusReachedAt(Lead $lead, LeadStatus $status): ?Carbon
    {
        return $this->activities($lead)
            ->where('type', LeadActivityType::StatusChanged)
            ->filter(function (LeadActivity $activity) use ($status): bool {
                return ($activity->metadata['to'] ?? null) === $status->value;
            })
            ->sortBy('created_at')
            ->first()?->created_at;
    }

    /**
     * @param  list<array{label: string, fresh_visits: int, revisits: int, total_visits: int}>  $projects
     */
    private function insight(Lead $lead, int $completedFollowUps, array $projects, ?Booking $booking): string
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

        if ($booking !== null) {
            $sentences[] = __('A booking was made for :detail.', [
                'detail' => $this->bookingDetail($booking),
            ]);

            if ($booking->hasAgreement()) {
                $sentences[] = __('The agreement has been marked.');
            }

            if ($booking->hasInvoice()) {
                $sentences[] = __('An invoice has been created.');
            }

            if ($booking->hasPaidPayout()) {
                $sentences[] = __('Payout has been received.');
            }
        }

        return implode(' ', $sentences);
    }
}
