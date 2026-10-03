<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TenantPermission;
use App\Enums\UtilityCredentialsDeliveryMode;
use App\Enums\UtilityMailEncryption;
use App\Models\UtilityMailSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUtilityMailSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission(TenantPermission::IntegrationsManage);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $hasExistingPassword = filled(UtilityMailSetting::current()?->password);

        return [
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:255'],
            'password' => [$hasExistingPassword ? 'nullable' : 'required', 'string', 'max:255'],
            'from_email' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'reply_to_email' => ['nullable', 'email', 'max:255'],
            'reply_to_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
            'encryption' => ['required', Rule::enum(UtilityMailEncryption::class)],
            'credentials_delivery_mode' => ['required', Rule::enum(UtilityCredentialsDeliveryMode::class)],
            'signature' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'remove_signature' => ['sometimes', 'boolean'],
        ];
    }
}
