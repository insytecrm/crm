<?php

namespace App\Http\Requests\Tenant;

use App\Enums\ScheduledActivityPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RescheduleScheduledActivityRequest extends FormRequest
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
            'scheduled_at' => ['required', 'date'],
            'priority' => ['nullable', Rule::enum(ScheduledActivityPriority::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('scheduled_at')) {
            $this->merge([
                'scheduled_at' => str_replace('T', ' ', $this->string('scheduled_at')->toString()),
            ]);
        }
    }
}
