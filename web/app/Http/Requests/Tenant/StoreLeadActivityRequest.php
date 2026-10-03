<?php

namespace App\Http\Requests\Tenant;

use App\Enums\LeadActivityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadActivityRequest extends FormRequest
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
            'type' => ['required', Rule::enum(LeadActivityType::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'redirect_url' => ['nullable', 'string', 'max:500'],
        ];
    }
}
