<?php

namespace App\Queries;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Support\DashboardPeriodFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DashboardPipeline
{
    /**
     * @return array{
     *     total: int,
     *     max_count: int,
     *     stages: list<array{
     *         status: string,
     *         label: string,
     *         count: int,
     *         percentage: float,
     *         bar_percent: float,
     *         bar_style: string,
     *         href: string,
     *     }>,
     * }
     */
    public function forTenant(DashboardPeriodFilter $filter): array
    {
        [$from, $to] = $filter->dateBounds();

        /** @var Collection<string, int> $counts */
        $counts = $this->baseQuery($from, $to)
            ->toBase()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn (mixed $count): int => (int) $count);

        $total = $counts->sum();
        $maxCount = max(1, (int) $counts->max());

        $stages = collect(LeadStatus::cases())
            ->map(function (LeadStatus $status) use ($counts, $total, $maxCount, $filter): array {
                $count = $counts->get($status->value, 0);
                $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0.0;
                $barPercent = $count > 0 ? round(($count / $maxCount) * 100, 1) : 0.0;

                return [
                    'status' => $status->value,
                    'label' => $status->label(),
                    'count' => $count,
                    'percentage' => $percentage,
                    'bar_percent' => $barPercent,
                    'bar_style' => $this->barStyle($status),
                    'href' => route('tenant.leads.index', array_merge(
                        ['status' => $status->value],
                        $filter->leadsDateQuery(),
                    )),
                ];
            })
            ->all();

        return [
            'total' => $total,
            'max_count' => (int) $counts->max(),
            'stages' => $stages,
        ];
    }

    private function baseQuery(?string $from, ?string $to): Builder
    {
        return Lead::query()
            ->when($from !== null, fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $from))
            ->when($to !== null, fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $to));
    }

    private function barStyle(LeadStatus $status): string
    {
        return match ($status) {
            LeadStatus::New => 'background-image: linear-gradient(90deg, #7dd3fc, #0ea5e9)',
            LeadStatus::Contacted => 'background-image: linear-gradient(90deg, #a5b4fc, #6366f1)',
            LeadStatus::Qualified => 'background-image: linear-gradient(90deg, #c4b5fd, #8b5cf6)',
            LeadStatus::FollowUp => 'background-image: linear-gradient(90deg, #fcd34d, #f59e0b)',
            LeadStatus::SiteVisit => 'background-image: linear-gradient(90deg, #fdba74, #f97316)',
            LeadStatus::Negotiation => 'background-image: linear-gradient(90deg, #d8b4fe, #a855f7)',
            LeadStatus::Converted => 'background-image: linear-gradient(90deg, #6ee7b7, #10b981)',
            LeadStatus::Lost => 'background-image: linear-gradient(90deg, #fda4af, #f43f5e)',
        };
    }
}
