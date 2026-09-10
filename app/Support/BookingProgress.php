<?php

namespace App\Support;

use App\Models\Booking;

class BookingProgress
{
    /**
     * @return array{completed: int, total: int}
     */
    public function for(Booking $booking): array
    {
        $completed = 1;

        if ($booking->hasAgreement()) {
            $completed++;
        }

        if ($booking->hasInvoice()) {
            $completed++;
        }

        if ($booking->hasPaidPayout()) {
            $completed++;
        }

        return [
            'completed' => $completed,
            'total' => 4,
        ];
    }
}
