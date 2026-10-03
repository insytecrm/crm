<?php

namespace App\Http\Requests\Platform;

use App\Enums\BillingCycle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuotationModalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_super_admin;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'platform_lead_id' => ['required', 'integer', Rule::exists('platform_leads', 'id')],
            'company_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'rera_number' => ['nullable', 'string', 'max:100'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')],
            'billing_cycle' => ['required', Rule::enum(BillingCycle::class)],
            'plan_price' => ['required', 'integer', 'min:0'],
            'discount_amount' => ['required', 'integer', 'min:0'],
            'tax_amount' => ['required', 'integer', 'min:0'],
            'valid_until' => ['required', 'date', 'after_or_equal:today'],
        ];
    }
}
