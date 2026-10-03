<?php

namespace App\Queries;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use App\Support\RevenueFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RevenueDashboard
{
    /**
     * @return array{
     *     developers: list<string>,
     *     properties: list<array{id: int, name: string}>,
     *     salespeople: list<array{id: int, name: string}>,
     * }
     */
    public function filterOptions(): array
    {
        $propertyIds = Booking::query()
            ->whereNotNull('agreement_date')
            ->distinct()
            ->pluck('property_id');

        $salespersonIds = Booking::query()
            ->whereNotNull('agreement_date')
            ->whereNotNull('created_by_id')
            ->distinct()
            ->pluck('created_by_id');

        $properties = Property::query()
            ->whereIn('id', $propertyIds)
            ->orderBy('project_name')
            ->get(['id', 'project_name', 'developer_name']);

        return [
            'developers' => $properties
                ->pluck('developer_name')
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all(),
            'properties' => $properties
                ->map(fn (Property $property): array => [
                    'id' => $property->id,
                    'name' => $property->project_name,
                ])
                ->all(),
            'salespeople' => User::query()
                ->whereIn('id', $salespersonIds)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                ])
                ->all(),
        ];
    }

    /**
     * @return array{
     *     total_revenue: int,
     *     revenue_this_month: int,
     *     revenue_this_quarter: int,
     *     total_commission: int,
     *     pending_commission: int,
     *     received_commission: int,
     * }
     */
    public function summary(RevenueFilter $filter): array
    {
        $base = $this->applyFilter(
            Booking::query()->whereNotNull('agreement_date'),
            $filter,
        );

        $now = now();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();
        $startOfQuarter = $now->copy()->firstOfQuarter()->toDateString();
        $endOfQuarter = $now->copy()->lastOfQuarter()->toDateString();

        return [
            'total_revenue' => (int) (clone $base)->sum('agreement_value'),
            'revenue_this_month' => (int) (clone $base)
                ->whereDate('agreement_date', '>=', $startOfMonth)
                ->whereDate('agreement_date', '<=', $endOfMonth)
                ->sum('agreement_value'),
            'revenue_this_quarter' => (int) (clone $base)
                ->whereDate('agreement_date', '>=', $startOfQuarter)
                ->whereDate('agreement_date', '<=', $endOfQuarter)
                ->sum('agreement_value'),
            'total_commission' => (int) (clone $base)->sum('payout_amount'),
            'pending_commission' => (int) (clone $base)->whereNull('payout_paid_at')->sum('payout_amount'),
            'received_commission' => (int) (clone $base)->whereNotNull('payout_paid_at')->sum('payout_amount'),
        ];
    }

    /**
     * @return list<array{key: string, label: string, revenue: int}>
     */
    public function trend(RevenueFilter $filter): array
    {
        $bookings = $this->applyFilter(
            Booking::query()->whereNotNull('agreement_date'),
            $filter,
        )->get(['agreement_date', 'agreement_value']);

        if ($bookings->isEmpty()) {
            return $this->emptyTrendMonths();
        }

        /** @var Collection<string, int> $grouped */
        $grouped = $bookings
            ->groupBy(fn (Booking $booking): string => $booking->agreement_date->format('Y-m'))
            ->map(fn (Collection $items): int => (int) $items->sum('agreement_value'));

        $start = Carbon::createFromFormat('Y-m', $grouped->keys()->min());
        $end = Carbon::createFromFormat('Y-m', $grouped->keys()->max());

        if ($filter->dateFrom !== null) {
            $start = Carbon::parse($filter->dateFrom)->startOfMonth();
        }

        if ($filter->dateTo !== null) {
            $end = Carbon::parse($filter->dateTo)->startOfMonth();
        }

        $points = [];
        $cursor = $start->copy()->startOfMonth();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');
            $points[] = [
                'key' => $key,
                'label' => $cursor->format('M Y'),
                'revenue' => (int) ($grouped[$key] ?? 0),
            ];
            $cursor->addMonth();
        }

        return $points;
    }

    /**
     * @return Collection<int, array{
     *     project: string,
     *     developer: ?string,
     *     bookings_count: int,
     *     sales_value: int,
     *     total_commission: int,
     *     pending_commission: int,
     *     received_commission: int,
     * }>
     */
    public function byProject(RevenueFilter $filter): Collection
    {
        $bookings = $this->applyFilter(
            Booking::query()
                ->whereNotNull('agreement_date')
                ->with('property:id,project_name,developer_name'),
            $filter,
        )->get();

        return $bookings
            ->groupBy('property_id')
            ->map(function (Collection $items): array {
                $property = $items->first()?->property;

                return [
                    'project' => $property?->project_name ?? __('Unknown Project'),
                    'developer' => $property?->developer_name,
                    'bookings_count' => $items->count(),
                    'sales_value' => (int) $items->sum('agreement_value'),
                    'total_commission' => (int) $items->sum('payout_amount'),
                    'pending_commission' => (int) $items->whereNull('payout_paid_at')->sum('payout_amount'),
                    'received_commission' => (int) $items->whereNotNull('payout_paid_at')->sum('payout_amount'),
                ];
            })
            ->sortByDesc('sales_value')
            ->values();
    }

    /**
     * @return Collection<int, array{
     *     id: ?int,
     *     name: string,
     *     revenue: int,
     *     bookings: int,
     *     sales_value: int,
     * }>
     */
    public function topSalespeople(RevenueFilter $filter): Collection
    {
        $bookings = $this->applyFilter(
            Booking::query()
                ->whereNotNull('agreement_date')
                ->with('createdBy:id,name'),
            $filter,
        )->get();

        return $bookings
            ->groupBy('created_by_id')
            ->map(function (Collection $items): array {
                $user = $items->first()?->createdBy;

                return [
                    'id' => $user?->id,
                    'name' => $user?->name ?? __('Unassigned'),
                    'revenue' => (int) $items->sum('payout_amount'),
                    'bookings' => $items->count(),
                    'sales_value' => (int) $items->sum('agreement_value'),
                ];
            })
            ->sortByDesc('revenue')
            ->values()
            ->take(10);
    }

    /**
     * @return list<array{key: string, label: string, revenue: int}>
     */
    private function emptyTrendMonths(): array
    {
        $points = [];
        $cursor = now()->copy()->subMonths(11)->startOfMonth();

        for ($index = 0; $index < 12; $index++) {
            $points[] = [
                'key' => $cursor->format('Y-m'),
                'label' => $cursor->format('M Y'),
                'revenue' => 0,
            ];
            $cursor->addMonth();
        }

        return $points;
    }

    /**
     * @param  Builder<Booking>  $query
     * @return Builder<Booking>
     */
    private function applyFilter(Builder $query, RevenueFilter $filter): Builder
    {
        if ($filter->dateFrom !== null) {
            $query->whereDate('agreement_date', '>=', $filter->dateFrom);
        }

        if ($filter->dateTo !== null) {
            $query->whereDate('agreement_date', '<=', $filter->dateTo);
        }

        if ($filter->developer !== null) {
            $query->whereHas('property', fn (Builder $propertyQuery): Builder => $propertyQuery
                ->where('developer_name', $filter->developer));
        }

        if ($filter->propertyId !== null) {
            $query->where('property_id', $filter->propertyId);
        }

        if ($filter->salespersonId !== null) {
            $query->where('created_by_id', $filter->salespersonId);
        }

        return $query;
    }
}
