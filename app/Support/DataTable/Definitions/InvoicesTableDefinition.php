<?php

namespace App\Support\DataTable\Definitions;

use App\Models\Booking;
use App\Models\User;
use App\Support\DataTable\AbstractDataTableDefinition;

class InvoicesTableDefinition extends AbstractDataTableDefinition
{
    public function key(): string
    {
        return 'invoices';
    }

    /**
     * @return array<string, bool>
     */
    public function defaultColumns(): array
    {
        return [
            'property' => true,
            'invoice_number' => true,
            'unit' => true,
            'lead' => true,
            'invoice_amount' => true,
            'invoice_date' => true,
            'agreement_date' => true,
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
            'invoice_number' => __('Invoice #'),
            'unit' => __('Unit'),
            'lead' => __('Lead'),
            'invoice_amount' => __('Invoice Amount'),
            'invoice_date' => __('Invoice Date'),
            'agreement_date' => __('Agreement Date'),
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
