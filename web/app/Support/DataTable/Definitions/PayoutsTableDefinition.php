<?php

namespace App\Support\DataTable\Definitions;

use App\Models\Booking;
use App\Models\User;
use App\Support\DataTable\AbstractDataTableDefinition;

class PayoutsTableDefinition extends AbstractDataTableDefinition
{
    public function key(): string
    {
        return 'payouts';
    }

    /**
     * @return array<string, bool>
     */
    public function defaultColumns(): array
    {
        return [
            'property' => true,
            'unit' => true,
            'lead' => true,
            'agreement_value' => true,
            'payout_amount' => true,
            'agreement_date' => true,
            'payout_status' => true,
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
            'unit' => __('Unit'),
            'lead' => __('Lead'),
            'agreement_value' => __('Agreement Value'),
            'payout_amount' => __('Payout Amount'),
            'agreement_date' => __('Agreement Date'),
            'payout_status' => __('Payout Status'),
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
