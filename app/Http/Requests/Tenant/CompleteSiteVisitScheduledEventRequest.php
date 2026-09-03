<?php

namespace App\Http\Requests\Tenant;

use App\Enums\ScheduledActivityPriority;
use App\Enums\SiteVisitNextStep;
use App\Enums\SiteVisitOutcome;
use App\Enums\SiteVisitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteSiteVisitScheduledEventRequest extends FormRequest
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
            'attended' => ['required', 'boolean'],
            'outcome' => [
                Rule::requiredIf(fn (): bool => $this->boolean('attended')),
                'nullable',
                Rule::enum(SiteVisitOutcome::class),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'next_step' => ['required', Rule::enum(SiteVisitNextStep::class)],
            'next_scheduled_at' => [
                Rule::requiredIf(fn (): bool => $this->requiresScheduledAt()),
                'nullable',
                'date',
            ],
            'next_priority' => [
                Rule::requiredIf(fn (): bool => $this->nextStepIs(SiteVisitNextStep::ScheduleFollowUp)),
                'nullable',
                Rule::enum(ScheduledActivityPriority::class),
            ],
            'next_property_id' => [
                Rule::requiredIf(fn (): bool => $this->nextStepIs(SiteVisitNextStep::ScheduleSiteVisit)),
                'nullable',
                'integer',
                'exists:properties,id',
            ],
            'next_visit_type' => [
                Rule::requiredIf(fn (): bool => $this->nextStepIs(SiteVisitNextStep::ScheduleSiteVisit)),
                'nullable',
                Rule::enum(SiteVisitType::class),
            ],
            'next_notes' => ['nullable', 'string', 'max:1000'],
            'task_title' => [
                Rule::requiredIf(fn (): bool => $this->nextStepIs(SiteVisitNextStep::CreateTask)),
                'nullable',
                'string',
                'max:255',
            ],
            'task_due_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('attended')) {
            $this->merge([
                'attended' => filter_var($this->input('attended'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }

        if ($this->boolean('attended') === false) {
            $this->merge(['outcome' => null]);
        }

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
        return $this->nextStepIs(SiteVisitNextStep::ScheduleFollowUp)
            || $this->nextStepIs(SiteVisitNextStep::ScheduleSiteVisit);
    }

    private function nextStepIs(SiteVisitNextStep $step): bool
    {
        return $this->input('next_step') === $step->value;
    }
}
