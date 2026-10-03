<?php

namespace App\Support;

use App\Enums\LeadActivityType;
use App\Enums\LeadScheduledEventStatus;
use App\Enums\LeadScheduledEventType;
use App\Enums\LeadStatus;
use App\Enums\ScheduledActivityOutcome;
use App\Enums\SiteVisitOutcome;
use App\Models\Lead;
use App\Models\LeadScheduledEvent;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class LeadScoring
{
    public const INTENT_LOOKBACK_DAYS = 90;

    public const MAX_RAW_POINTS = 250;

    /**
     * @return array<string, int>
     */
    public static function followUpIntentPoints(): array
    {
        return [
            ScheduledActivityOutcome::ReadyToVisit->value => 60,
            ScheduledActivityOutcome::CallbackRequested->value => 40,
            ScheduledActivityOutcome::Interested->value => 25,
        ];
    }

    /**
     * @return array<string, int>
     */
    public static function siteVisitIntentPoints(): array
    {
        return [
            SiteVisitOutcome::ReadyToBook->value => 100,
            SiteVisitOutcome::Interested->value => 25,
        ];
    }

    /**
     * @return list<string>
     */
    public static function negativeFollowUpOutcomes(): array
    {
        return [
            ScheduledActivityOutcome::NotInterested->value,
            ScheduledActivityOutcome::WrongNumber->value,
        ];
    }

    /**
     * @return list<string>
     */
    public static function negativeSiteVisitOutcomes(): array
    {
        return [
            SiteVisitOutcome::NotInterested->value,
            SiteVisitOutcome::PriceConcern->value,
            SiteVisitOutcome::LocationConcern->value,
        ];
    }

    /**
     * @return array{
     *     lead_score: int,
     *     lead_score_intent: int,
     *     latest_positive_outcome_at: ?CarbonInterface,
     *     latest_positive_outcome: ?string,
     * }
     */
    public static function calculate(Lead $lead): array
    {
        $completedEvents = self::completedOutcomeEvents($lead);
        $latestOutcomeEvent = $completedEvents->first();
        $latestOutcomeValue = self::eventOutcomeValue($latestOutcomeEvent);
        $latestIsNegative = $latestOutcomeValue !== null && self::isNegativeOutcome(
            $latestOutcomeEvent?->type,
            $latestOutcomeValue,
        );

        $intentPoints = 0.0;
        $latestPositiveOutcomeAt = null;
        $latestPositiveOutcome = null;
        $lookbackStart = now()->subDays(self::INTENT_LOOKBACK_DAYS);

        if (! $latestIsNegative) {
            foreach ($completedEvents as $event) {
                $outcomeValue = self::eventOutcomeValue($event);

                if ($outcomeValue === null || ! self::isPositiveOutcome($event->type, $outcomeValue)) {
                    continue;
                }

                $completedAt = $event->completed_at;

                if (! $completedAt instanceof CarbonInterface || $completedAt->lt($lookbackStart)) {
                    continue;
                }

                $basePoints = self::intentPointsFor($event->type, $outcomeValue);
                $intentPoints += $basePoints * self::decayMultiplier($completedAt);

                if ($latestPositiveOutcomeAt === null || $completedAt->gt($latestPositiveOutcomeAt)) {
                    $latestPositiveOutcomeAt = $completedAt;
                    $latestPositiveOutcome = $outcomeValue;
                }
            }
        }

        $rawPoints = $intentPoints
            + self::pipelinePoints($lead->status)
            + self::engagementPoints($lead, $completedEvents)
            + self::fitPoints($lead);

        if ($latestIsNegative && $latestOutcomeValue !== null) {
            $rawPoints += self::negativeOutcomePenalty($latestOutcomeEvent?->type, $latestOutcomeValue);
        }

        $rawPoints = max(0, $rawPoints);

        $attributes = [
            'lead_score' => min(100, (int) round(($rawPoints / self::MAX_RAW_POINTS) * 100)),
            'lead_score_intent' => (int) round($intentPoints),
            'latest_positive_outcome_at' => $latestPositiveOutcomeAt,
            'latest_positive_outcome' => $latestPositiveOutcome,
        ];

        if ($lead->status?->isClosed()) {
            $attributes['lead_score_intent'] = 0;
            $attributes['latest_positive_outcome_at'] = null;
            $attributes['latest_positive_outcome'] = null;
        }

        return $attributes;
    }

    public static function qualifiesForPriority(Lead $lead): bool
    {
        if ($lead->status?->isClosed()) {
            return false;
        }

        if ((int) $lead->lead_score_intent <= 0) {
            return false;
        }

        $latestPositiveAt = $lead->latest_positive_outcome_at;

        if (! $latestPositiveAt instanceof CarbonInterface) {
            return false;
        }

        return $latestPositiveAt->gte(now()->subDays(self::INTENT_LOOKBACK_DAYS));
    }

    /**
     * @return Collection<int, LeadScheduledEvent>
     */
    private static function completedOutcomeEvents(Lead $lead): Collection
    {
        $events = $lead->relationLoaded('scheduledEvents')
            ? $lead->scheduledEvents
            : $lead->scheduledEvents()->get();

        return $events
            ->filter(function (LeadScheduledEvent $event): bool {
                return $event->status === LeadScheduledEventStatus::Completed
                    && filled(self::eventOutcomeValue($event));
            })
            ->sortByDesc(fn (LeadScheduledEvent $event): string => sprintf(
                '%s-%010d',
                $event->completed_at?->format('Y-m-d H:i:s.u') ?? '',
                $event->id,
            ))
            ->values();
    }

    private static function eventOutcomeValue(?LeadScheduledEvent $event): ?string
    {
        if (! $event instanceof LeadScheduledEvent) {
            return null;
        }

        $outcome = $event->completion_outcome;

        return $outcome instanceof \BackedEnum ? $outcome->value : null;
    }

    private static function isPositiveOutcome(?LeadScheduledEventType $type, string $outcomeValue): bool
    {
        return match ($type) {
            LeadScheduledEventType::SiteVisit => array_key_exists($outcomeValue, self::siteVisitIntentPoints()),
            LeadScheduledEventType::FollowUp => array_key_exists($outcomeValue, self::followUpIntentPoints()),
            default => false,
        };
    }

    private static function isNegativeOutcome(?LeadScheduledEventType $type, string $outcomeValue): bool
    {
        return match ($type) {
            LeadScheduledEventType::SiteVisit => in_array($outcomeValue, self::negativeSiteVisitOutcomes(), true),
            LeadScheduledEventType::FollowUp => in_array($outcomeValue, self::negativeFollowUpOutcomes(), true),
            default => false,
        };
    }

    private static function intentPointsFor(?LeadScheduledEventType $type, string $outcomeValue): int
    {
        return match ($type) {
            LeadScheduledEventType::SiteVisit => self::siteVisitIntentPoints()[$outcomeValue] ?? 0,
            LeadScheduledEventType::FollowUp => self::followUpIntentPoints()[$outcomeValue] ?? 0,
            default => 0,
        };
    }

    private static function negativeOutcomePenalty(?LeadScheduledEventType $type, string $outcomeValue): int
    {
        return match ($type) {
            LeadScheduledEventType::FollowUp => match ($outcomeValue) {
                ScheduledActivityOutcome::NotInterested->value => -50,
                ScheduledActivityOutcome::WrongNumber->value => -30,
                default => 0,
            },
            LeadScheduledEventType::SiteVisit => match ($outcomeValue) {
                SiteVisitOutcome::NotInterested->value => -50,
                SiteVisitOutcome::PriceConcern->value,
                SiteVisitOutcome::LocationConcern->value => -20,
                default => 0,
            },
            default => 0,
        };
    }

    private static function decayMultiplier(CarbonInterface $completedAt): float
    {
        $days = $completedAt->diffInDays(now());

        return match (true) {
            $days <= 7 => 1.0,
            $days <= 30 => 0.7,
            $days <= 90 => 0.4,
            default => 0.2,
        };
    }

    private static function pipelinePoints(?LeadStatus $status): int
    {
        return match ($status) {
            LeadStatus::New => 0,
            LeadStatus::Contacted => 5,
            LeadStatus::Qualified => 10,
            LeadStatus::FollowUp => 15,
            LeadStatus::SiteVisit => 25,
            LeadStatus::Negotiation => 40,
            default => 0,
        };
    }

    /**
     * @param  Collection<int, LeadScheduledEvent>  $completedEvents
     */
    private static function engagementPoints(Lead $lead, Collection $completedEvents): int
    {
        $points = 0;

        $points += min(30, $completedEvents
            ->where('type', LeadScheduledEventType::FollowUp)
            ->count() * 3);

        $points += $completedEvents
            ->where('type', LeadScheduledEventType::SiteVisit)
            ->filter(fn (LeadScheduledEvent $event): bool => (bool) $event->attended)
            ->count() * 10;

        if ($lead->upcoming_site_visit_at !== null && $lead->upcoming_site_visit_at->isFuture()) {
            $points += 8;
        }

        if ($lead->bookings()->exists()) {
            $points += 80;
        }

        $communicationCount = $lead->activities()
            ->whereIn('type', [LeadActivityType::CallMade, LeadActivityType::WhatsAppMessage])
            ->count();

        $points += min(10, $communicationCount * 2);

        $propertySharedCount = $lead->activities()
            ->where('type', LeadActivityType::PropertyShared)
            ->count();

        $points += min(15, $propertySharedCount * 5);

        return $points;
    }

    private static function fitPoints(Lead $lead): int
    {
        $points = 0;

        if (filled($lead->budget)) {
            $points += 5;
        }

        if (filled($lead->location)) {
            $points += 3;
        }

        if (filled($lead->property_type) && filled($lead->configuration)) {
            $points += 5;
        }

        return $points;
    }
}
