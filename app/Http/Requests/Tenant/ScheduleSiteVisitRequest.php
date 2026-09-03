<?php

namespace App\Http\Requests\Tenant;

use App\Enums\SiteVisitType;
use App\Support\ReminderBefore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ScheduleSiteVisitRequest extends FormRequest
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
            'upcoming_site_visit_at' => ['required', 'date'],
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'visit_type' => ['required', Rule::enum(SiteVisitType::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
            ...ReminderBefore::validationRules(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            ReminderBefore::afterValidation(
                $validator,
                $this->date('upcoming_site_visit_at'),
                'upcoming_site_visit_at',
            );
        });
    }

    protected function prepareForValidation(): void
    {
        ReminderBefore::prepare($this);

        if ($this->filled('upcoming_site_visit_at')) {
            $this->merge([
                'upcoming_site_visit_at' => str_replace('T', ' ', $this->string('upcoming_site_visit_at')->toString()),
            ]);
        }
    }
}
