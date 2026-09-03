<?php

namespace App\Http\Requests\Tenant;

use App\Enums\LeadClosingReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CloseLeadRequest extends FormRequest
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
            'closing_reason' => ['required', Rule::enum(LeadClosingReason::class)],
        ];
    }
}
