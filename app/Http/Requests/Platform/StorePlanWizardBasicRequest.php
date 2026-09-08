<?php

namespace App\Http\Requests\Platform;

use App\Support\Platform\PlanFormRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePlanWizardBasicRequest extends FormRequest
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
        return PlanFormRules::basic();
    }
}
