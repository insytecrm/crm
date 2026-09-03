<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'invoice_number' => ['required', 'string', 'max:50'],
            'invoice_date' => ['required', 'date'],
            'agreement_value' => ['required', 'integer', 'min:1'],
            'payout_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'payout_amount' => ['required', 'integer', 'min:0'],
            'invoice_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
