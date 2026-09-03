<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

abstract class BulkLeadsRequest extends FormRequest
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
            'lead_ids' => ['required', 'array', 'min:1'],
            'lead_ids.*' => ['required', 'integer', 'exists:leads,id'],
            'listing' => ['nullable', 'string'],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return list<int>
     */
    public function leadIds(): array
    {
        return array_map('intval', $this->validated('lead_ids'));
    }
}
