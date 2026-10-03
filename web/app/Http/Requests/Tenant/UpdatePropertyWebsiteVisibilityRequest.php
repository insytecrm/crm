<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyWebsiteVisibilityRequest extends FormRequest
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
            'show_on_website' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
        ];
    }

    public function showOnWebsite(): bool
    {
        return filter_var($this->validated('show_on_website'), FILTER_VALIDATE_BOOLEAN);
    }
}
