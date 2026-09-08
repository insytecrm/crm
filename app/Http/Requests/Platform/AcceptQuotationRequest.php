<?php

namespace App\Http\Requests\Platform;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AcceptQuotationRequest extends FormRequest
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
            'accepted_by_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
