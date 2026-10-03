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
            'invoice_number' => true,
            'lead' => true,
            'property' => true,
            'unit' => true,
            'agreement_value' => true,
            'invoice_amount' => true,
            'agreement_date' => true,
            'invoice_date' => true,
            'payment_status' => true,
            'actions' => true,
        ];
    }

    /**
     * @return list<string>
     */
    public function requiredColumns(): array
    {
        return ['invoice_number'];
    }

    /**
     * @return array<string, string>
     */
    public function columnLabels(): array
    {
        return [
            'invoice_number' => __('Invoice Number'),
            'lead' => __('Lead Name'),
            'property' => __('Property'),
            'unit' => __('Unit Number'),
            'agreement_value' => __('Agreement Value'),
            'invoice_amount' => __('Invoice Amount'),
            'agreement_date' => __('Agreement Date'),
            'invoice_date' => __('Invoice Date'),
            'payment_status' => __('Payment Status'),
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
