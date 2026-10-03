<?php

namespace App\Queries;

use App\Models\Booking;

class BookingStatistics
{
    /**
     * @return array{
     *     total: int,
     *     agreement_done: int,
     *     invoice_created: int,
     *     booking_value: int,
     *     bookings_this_month: int,
     * }
     */
    public function forTenant(): array
    {
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $row = Booking::query()
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(CASE WHEN agreement_date IS NOT NULL AND invoiced_at IS NULL THEN 1 ELSE 0 END), 0) as agreement_done')
            ->selectRaw('COALESCE(SUM(CASE WHEN invoiced_at IS NOT NULL THEN 1 ELSE 0 END), 0) as invoice_created')
            ->selectRaw('COALESCE(SUM(agreement_value), 0) as booking_value')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN booking_date >= ? AND booking_date <= ? THEN 1 ELSE 0 END), 0) as bookings_this_month',
                [$startOfMonth, $endOfMonth],
            )
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'agreement_done' => (int) ($row->agreement_done ?? 0),
            'invoice_created' => (int) ($row->invoice_created ?? 0),
            'booking_value' => (int) ($row->booking_value ?? 0),
            'bookings_this_month' => (int) ($row->bookings_this_month ?? 0),
        ];
    }
}
