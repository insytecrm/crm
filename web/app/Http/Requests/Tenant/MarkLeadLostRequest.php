<?php

namespace App\Http\Requests\Tenant;

use App\Enums\LeadLostReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarkLeadLostRequest extends FormRequest
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
            'lost_reasons' => ['required', 'array', 'min:1'],
            'lost_reasons.*' => ['required', Rule::enum(LeadLostReason::class)],
            'closing_notes' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lost_reasons.required' => __('Select at least one lost reason.'),
            'lost_reasons.min' => __('Select at least one lost reason.'),
            'closing_notes.required' => __('An additional note is required.'),
        ];
    }
}
