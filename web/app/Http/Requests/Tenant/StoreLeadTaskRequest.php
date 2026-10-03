<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadTaskRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_at' => ['nullable', 'date'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('due_at')) {
            $this->merge([
                'due_at' => str_replace('T', ' ', $this->string('due_at')->toString()),
            ]);
        }
    }
}
