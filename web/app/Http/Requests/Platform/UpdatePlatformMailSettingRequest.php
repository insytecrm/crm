<?php

namespace App\Http\Requests\Platform;

use App\Enums\UtilityCredentialsDeliveryMode;
use App\Enums\UtilityMailEncryption;
use App\Models\PlatformMailSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformMailSettingRequest extends FormRequest
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
        $hasExistingPassword = filled(PlatformMailSetting::current()?->password);

        return [
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:255'],
            'password' => [$hasExistingPassword ? 'nullable' : 'required', 'string', 'max:255'],
            'from_email' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'encryption' => ['required', Rule::enum(UtilityMailEncryption::class)],
            'credentials_delivery_mode' => ['required', Rule::enum(UtilityCredentialsDeliveryMode::class)],
            'signature' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'remove_signature' => ['sometimes', 'boolean'],
        ];
    }
}
