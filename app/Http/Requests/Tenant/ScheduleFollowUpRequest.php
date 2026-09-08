<?php

namespace App\Http\Requests\Tenant;

use App\Enums\ScheduledActivityPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScheduleFollowUpRequest extends FormRequest
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
            'next_follow_up_at' => ['required', 'date'],
            'priority' => ['required', Rule::enum(ScheduledActivityPriority::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('next_follow_up_at')) {
            $this->merge([
                'next_follow_up_at' => str_replace('T', ' ', $this->string('next_follow_up_at')->toString()),
            ]);
        }
    }
}
