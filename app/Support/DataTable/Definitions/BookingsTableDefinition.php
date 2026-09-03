<?php

namespace App\Support\DataTable\Definitions;

use App\Models\Booking;
use App\Models\User;
use App\Support\DataTable\AbstractDataTableDefinition;

class BookingsTableDefinition extends AbstractDataTableDefinition
{
    public function key(): string
    {
        return 'bookings';
    }

    /**
     * @return array<string, bool>
     */
    public function defaultColumns(): array
    {
        return [
            'property' => true,
            'configuration' => true,
            'unit' => true,
            'agreement_value' => true,
            'booking_date' => true,
            'lead' => true,
            'payout_amount' => false,
            'actions' => true,
        ];
    }

    /**
     * @return list<string>
     */
    public function requiredColumns(): array
    {
        return ['property'];
    }

    /**
     * @return array<string, string>
     */
    public function columnLabels(): array
    {
        return [
            'property' => __('Property'),
            'configuration' => __('Configuration'),
            'unit' => __('Unit'),
            'agreement_value' => __('Agreement Value'),
            'booking_date' => __('Booking Date'),
            'lead' => __('Lead'),
            'payout_amount' => __('Payout Amount'),
            'actions' => __('Actions'),
        ];
    }

    public function modelClass(): string
    {
        return Booking::class;
    }

    public function bulkDeleteParameterName(): string
    {
        return 'booking_ids';
    }

    public function authorizeBulkDelete(User $user): bool
    {
        return true;
    }
}
