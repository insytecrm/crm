<?php

namespace App\Http\Requests\Tenant;

use App\Support\ReminderBefore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            ...ReminderBefore::validationRules(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            ReminderBefore::afterValidation(
                $validator,
                $this->date('due_at'),
                'due_at',
            );
        });
    }

    protected function prepareForValidation(): void
    {
        ReminderBefore::prepare($this);

        if ($this->filled('due_at')) {
            $this->merge([
                'due_at' => str_replace('T', ' ', $this->string('due_at')->toString()),
            ]);
        }
    }
}
