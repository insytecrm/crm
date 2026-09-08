<?php

namespace App\Http\Requests\Tenant;

use App\Enums\DomainPurpose;
use App\Enums\TenantPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifySettingsDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(TenantPermission::SettingsCompany) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'purpose' => ['required', Rule::enum(DomainPurpose::class)],
        ];
    }
}
