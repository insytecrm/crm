<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QueryableDate
{
    /**
     * Apply an inclusive calendar-day filter equivalent to whereDate().
     *
     * @param  EloquentBuilder<*>|QueryBuilder  $query
     */
    public static function constrain(EloquentBuilder|QueryBuilder $query, string $column, ?string $from, ?string $to): void
    {
        if ($from !== null) {
            $start = self::startOfDay($from);

            if ($start === null) {
                $query->whereDate($column, '>=', $from);
            } else {
                $query->where($column, '>=', $start);
            }
        }

        if ($to !== null) {
            $end = self::startOfNextDay($to);

            if ($end === null) {
                $query->whereDate($column, '<=', $to);
            } else {
                $query->where($column, '<', $end);
            }
        }
    }

    /**
     * Restrict a timestamp column to a single calendar day.
     *
     * @param  EloquentBuilder<*>|QueryBuilder  $query
     */
    public static function constrainDay(EloquentBuilder|QueryBuilder $query, string $column, string $date): void
    {
        self::constrain($query, $column, $date, $date);
    }

    /**
     * Count rows grouped by calendar day or month of a timestamp column.
     *
     * @param  EloquentBuilder<*>  $query
     * @param  'day'|'month'  $bucket
     * @return Collection<string, int>
     */
    public static function countsByBucket(EloquentBuilder $query, string $column, string $bucket): Collection
    {
        $expression = self::bucketExpression($column, $bucket);

        return $query->toBase()
            ->selectRaw("{$expression} as bucket, count(*) as aggregate")
            ->groupByRaw($expression)
            ->pluck('aggregate', 'bucket')
            ->map(fn (mixed $count): int => (int) $count);
    }

    /**
     * Average a positive second difference between two timestamp columns.
     *
     * @param  EloquentBuilder<*>|QueryBuilder  $query
     */
    public static function averagePositiveDiffInSeconds(
        EloquentBuilder|QueryBuilder $query,
        string $startColumn,
        string $endColumn,
    ): ?int {
        $grammar = $query instanceof EloquentBuilder
            ? $query->getQuery()->getGrammar()
            : $query->getGrammar();
        $start = $grammar->wrap($startColumn);
        $end = $grammar->wrap($endColumn);
        $driver = $query->getConnection()->getDriverName();

        $expression = match ($driver) {
            'sqlite' => "avg(max(0, cast(strftime('%s', {$end}) as integer) - cast(strftime('%s', {$start}) as integer)))",
            'pgsql' => "avg(greatest(0, extract(epoch from ({$end} - {$start}))))",
            default => "avg(greatest(0, timestampdiff(second, {$start}, {$end})))",
        };

        $value = $query->clone()->selectRaw("{$expression} as aggregate")->value('aggregate');

        if ($value === null) {
            return null;
        }

        return (int) round((float) $value);
    }

    /**
     * Count active-lead aging buckets using the same day boundaries as Carbon::diffInDays().
     *
     * @param  EloquentBuilder<*>  $query
     * @return array{0_7: int, 8_14: int, 15_30: int, 31_60: int, 60_plus: int}
     */
    public static function agingBucketCounts(EloquentBuilder $query, string $column): array
    {
        $today = now()->startOfDay();
        $sevenDaysAgo = $today->copy()->subDays(7);
        $fourteenDaysAgo = $today->copy()->subDays(14);
        $thirtyDaysAgo = $today->copy()->subDays(30);
        $sixtyDaysAgo = $today->copy()->subDays(60);
        $wrapped = DB::connection()->getQueryGrammar()->wrap($column);

        $row = $query->toBase()
            ->selectRaw(
                "coalesce(sum(case when {$wrapped} >= ? then 1 else 0 end), 0) as c_0_7,
                 coalesce(sum(case when {$wrapped} >= ? and {$wrapped} < ? then 1 else 0 end), 0) as c_8_14,
                 coalesce(sum(case when {$wrapped} >= ? and {$wrapped} < ? then 1 else 0 end), 0) as c_15_30,
                 coalesce(sum(case when {$wrapped} >= ? and {$wrapped} < ? then 1 else 0 end), 0) as c_31_60,
                 coalesce(sum(case when {$wrapped} < ? then 1 else 0 end), 0) as c_60_plus",
                [
                    $sevenDaysAgo,
                    $fourteenDaysAgo,
                    $sevenDaysAgo,
                    $thirtyDaysAgo,
                    $fourteenDaysAgo,
                    $sixtyDaysAgo,
                    $thirtyDaysAgo,
                    $sixtyDaysAgo,
                ],
            )
            ->first();

        return [
            '0_7' => (int) ($row->c_0_7 ?? 0),
            '8_14' => (int) ($row->c_8_14 ?? 0),
            '15_30' => (int) ($row->c_15_30 ?? 0),
            '31_60' => (int) ($row->c_31_60 ?? 0),
            '60_plus' => (int) ($row->c_60_plus ?? 0),
        ];
    }

    /**
     * @param  'day'|'month'  $bucket
     */
    private static function bucketExpression(string $column, string $bucket): string
    {
        $wrapped = DB::connection()->getQueryGrammar()->wrap($column);
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'sqlite' => $bucket === 'day'
                ? "strftime('%Y-%m-%d', {$wrapped})"
                : "strftime('%Y-%m', {$wrapped})",
            'pgsql' => $bucket === 'day'
                ? "to_char({$wrapped}, 'YYYY-MM-DD')"
                : "to_char({$wrapped}, 'YYYY-MM')",
            default => $bucket === 'day'
                ? "date_format({$wrapped}, '%Y-%m-%d')"
                : "date_format({$wrapped}, '%Y-%m')",
        };
    }

    private static function startOfDay(string $date): ?Carbon
    {
        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function startOfNextDay(string $date): ?Carbon
    {
        $start = self::startOfDay($date);

        return $start?->copy()->addDay();
    }
}
