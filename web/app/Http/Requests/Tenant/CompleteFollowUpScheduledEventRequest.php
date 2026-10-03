<?php

namespace App\Http\Requests\Tenant;

use App\Enums\ScheduledActivityContactMethod;
use App\Enums\ScheduledActivityNextStep;
use App\Enums\ScheduledActivityOutcome;
use App\Enums\ScheduledActivityPriority;
use App\Enums\SiteVisitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteFollowUpScheduledEventRequest extends FormRequest
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
            'contact_method' => ['required', Rule::enum(ScheduledActivityContactMethod::class)],
            'outcome' => ['required', Rule::enum(ScheduledActivityOutcome::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'next_step' => ['required', Rule::enum(ScheduledActivityNextStep::class)],
            'next_scheduled_at' => [
                Rule::requiredIf(fn (): bool => $this->requiresScheduledAt()),
                'nullable',
                'date',
            ],
            'next_priority' => [
                Rule::requiredIf(fn (): bool => $this->nextStepIs(ScheduledActivityNextStep::ScheduleFollowUp)),
                'nullable',
                Rule::enum(ScheduledActivityPriority::class),
            ],
            'next_property_id' => [
                Rule::requiredIf(fn (): bool => $this->nextStepIs(ScheduledActivityNextStep::ScheduleSiteVisit)),
                'nullable',
                'integer',
                Rule::exists('properties', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'next_visit_type' => [
                Rule::requiredIf(fn (): bool => $this->nextStepIs(ScheduledActivityNextStep::ScheduleSiteVisit)),
                'nullable',
                Rule::enum(SiteVisitType::class),
            ],
            'next_notes' => ['nullable', 'string', 'max:1000'],
            'task_title' => [
                Rule::requiredIf(fn (): bool => $this->nextStepIs(ScheduledActivityNextStep::CreateTask)),
                'nullable',
                'string',
                'max:255',
            ],
            'task_due_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('next_scheduled_at')) {
            $this->merge([
                'next_scheduled_at' => str_replace('T', ' ', $this->string('next_scheduled_at')->toString()),
            ]);
        }

        if ($this->filled('task_due_at')) {
            $this->merge([
                'task_due_at' => str_replace('T', ' ', $this->string('task_due_at')->toString()),
            ]);
        }
    }

    private function requiresScheduledAt(): bool
    {
        return $this->nextStepIs(ScheduledActivityNextStep::ScheduleFollowUp)
            || $this->nextStepIs(ScheduledActivityNextStep::ScheduleSiteVisit);
    }

    private function nextStepIs(ScheduledActivityNextStep $step): bool
    {
        return $this->input('next_step') === $step->value;
    }
}
