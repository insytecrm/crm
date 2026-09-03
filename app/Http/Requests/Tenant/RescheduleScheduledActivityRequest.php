<?php

namespace App\Http\Requests\Tenant;

use App\Enums\ScheduledActivityPriority;
use App\Support\ReminderBefore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            ...ReminderBefore::validationRules(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            ReminderBefore::afterValidation(
                $validator,
                $this->date('scheduled_at'),
                'scheduled_at',
            );
        });
    }

    protected function prepareForValidation(): void
    {
        ReminderBefore::prepare($this);

        if ($this->filled('scheduled_at')) {
            $this->merge([
                'scheduled_at' => str_replace('T', ' ', $this->string('scheduled_at')->toString()),
            ]);
        }
    }
}
