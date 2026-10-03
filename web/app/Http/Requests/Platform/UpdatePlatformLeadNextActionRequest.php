<?php

namespace App\Http\Requests\Platform;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePlatformLeadNextActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_super_admin;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'next_action_label' => ['required', 'string', 'max:255'],
            'next_action_at' => ['nullable', 'date'],
            'next_action_time' => ['nullable', 'date_format:H:i'],
        ];
    }
}
