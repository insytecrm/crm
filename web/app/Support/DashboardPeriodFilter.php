<?php

namespace App\Support;

use App\Enums\DashboardPeriod;
use App\Models\SalesTeam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardPeriodFilter
{
    public function __construct(
        public readonly DashboardPeriod $period = DashboardPeriod::ThisMonth,
        public readonly ?string $from = null,
        public readonly ?string $to = null,
        public readonly ?int $teamId = null,
        public readonly ?int $userId = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $period = DashboardPeriod::fromRequest($request->string('period')->toString());
        $from = filled($request->input('from')) ? (string) $request->input('from') : null;
        $to = filled($request->input('to')) ? (string) $request->input('to') : null;

        if ($period === DashboardPeriod::Custom && $from === null && $to === null) {
            $period = DashboardPeriod::ThisMonth;
        }

        return new self(
            period: $period,
            from: $from,
            to: $to,
        );
    }

    public static function fromReportsRequest(Request $request): self
    {
        $raw = $request->string('period')->toString();
        $period = $raw !== ''
            ? (DashboardPeriod::tryFrom($raw) ?? DashboardPeriod::AllTime)
            : DashboardPeriod::AllTime;

        if ($period === DashboardPeriod::Custom) {
            $period = DashboardPeriod::AllTime;
        }

        return new self(period: $period);
    }

    public static function fromAnalyticsRequest(Request $request): self
    {
        $base = self::fromReportsRequest($request);

        $teamId = self::positiveIntOrNull($request->input('team'));
        $userId = self::positiveIntOrNull($request->input('user'));

        if ($teamId !== null && ! SalesTeam::query()->whereKey($teamId)->exists()) {
            $teamId = null;
        }

        if ($userId !== null && ! User::query()->whereKey($userId)->exists()) {
            $userId = null;
        }

        return new self(
            period: $base->period,
            from: $base->from,
            to: $base->to,
            teamId: $teamId,
            userId: $userId,
        );
    }

    public function isReportsFiltered(): bool
    {
        return $this->period !== DashboardPeriod::AllTime;
    }

    public function isAnalyticsFiltered(): bool
    {
        return $this->isReportsFiltered()
            || $this->teamId !== null
            || $this->userId !== null;
    }

    public function analyticsActiveCount(): int
    {
        return collect([
            $this->isReportsFiltered() ? $this->period->value : null,
            $this->teamId,
            $this->userId,
        ])->filter(fn (mixed $value): bool => filled($value))->count();
    }

    /**
     * @return array<string, int|string>
     */
    public function toReportsQueryArray(): array
    {
        if (! $this->isReportsFiltered()) {
            return [];
        }

        return ['period' => $this->period->value];
    }

    /**
     * @return array<string, int|string>
     */
    public function toAnalyticsQueryArray(): array
    {
        return array_filter([
            'period' => $this->isReportsFiltered() ? $this->period->value : null,
            'team' => $this->teamId,
            'user' => $this->userId,
        ], fn (mixed $value): bool => filled($value));
    }

    /**
     * @return list<DashboardPeriod>
     */
    public static function reportsSelectablePeriods(): array
    {
        return [
            DashboardPeriod::Today,
            DashboardPeriod::ThisWeek,
            DashboardPeriod::ThisMonth,
            DashboardPeriod::ThisQuarter,
            DashboardPeriod::ThisYear,
            DashboardPeriod::AllTime,
        ];
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    public function dateBounds(): array
    {
        $now = now();

        return match ($this->period) {
            DashboardPeriod::Today => [
                $now->toDateString(),
                $now->toDateString(),
            ],
            DashboardPeriod::ThisWeek => [
                $now->copy()->startOfWeek()->toDateString(),
                $now->copy()->endOfWeek()->toDateString(),
            ],
            DashboardPeriod::ThisMonth => [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
            ],
            DashboardPeriod::ThisQuarter => [
                $now->copy()->firstOfQuarter()->toDateString(),
                $now->copy()->lastOfQuarter()->toDateString(),
            ],
            DashboardPeriod::ThisYear => [
                $now->copy()->startOfYear()->toDateString(),
                $now->copy()->endOfYear()->toDateString(),
            ],
            DashboardPeriod::AllTime => [null, null],
            DashboardPeriod::Custom => [
                $this->validDate($this->from),
                $this->validDate($this->to),
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toQueryArray(): array
    {
        $query = ['period' => $this->period->value];

        if ($this->period === DashboardPeriod::Custom) {
            $query['from'] = $this->from;
            $query['to'] = $this->to;
        }

        return array_filter($query, fn (mixed $value): bool => filled($value));
    }

    /**
     * @return array<string, mixed>
     */
    public function leadsDateQuery(): array
    {
        [$from, $to] = $this->dateBounds();

        return array_filter([
            'created_from' => $from,
            'created_to' => $to,
        ], fn (mixed $value): bool => filled($value));
    }

    private function validDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function positiveIntOrNull(mixed $value): ?int
    {
        if (! filled($value) || ! is_numeric($value)) {
            return null;
        }

        $int = (int) $value;

        return $int > 0 ? $int : null;
    }
}
