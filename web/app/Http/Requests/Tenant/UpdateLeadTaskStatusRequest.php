<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadTaskStatusRequest extends FormRequest
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
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'notes' => [
                Rule::requiredIf(fn (): bool => $this->input('status') === TaskStatus::Cancelled->value),
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
