<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLandingTrialRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'team_size' => ['required', 'string', Rule::in(['just_me', '2_5', '6_15', '16_plus'])],
            'plan' => ['required', 'string', Rule::in(['starter', 'growth', 'pro'])],
            'billing_cycle' => ['required', 'string', Rule::in(['monthly', 'yearly'])],
        ];
    }
}
