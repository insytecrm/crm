<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class MarkBookingAgreementRequest extends FormRequest
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
            'agreement_date' => ['required', 'date'],
            'agreement_value' => ['required', 'integer', 'min:1'],
            'payout_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'payout_amount' => ['required', 'integer', 'min:0'],
        ];
    }
}
