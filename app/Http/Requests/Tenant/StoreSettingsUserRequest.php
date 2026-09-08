<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TenantPermission;
use Illuminate\Foundation\Http\FormRequest;

class StoreSettingsUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(TenantPermission::SettingsUsers) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'exists:roles,id'],
        ];
    }
}
