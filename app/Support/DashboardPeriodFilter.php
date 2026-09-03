<?php

namespace App\Support;

use App\Enums\DashboardPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardPeriodFilter
{
    public function __construct(
        public readonly DashboardPeriod $period = DashboardPeriod::ThisMonth,
        public readonly ?string $from = null,
        public readonly ?string $to = null,
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
}
