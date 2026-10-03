<?php

namespace App\Http\Requests\Platform;

use App\Enums\BillingCycle;
use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangePartnerSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', Rule::exists(Plan::class, 'id')],
            'billing_cycle' => ['required', Rule::enum(BillingCycle::class)],
        ];
    }
}
