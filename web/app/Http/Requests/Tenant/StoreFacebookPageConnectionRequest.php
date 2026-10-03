<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TenantPermission;
use Illuminate\Foundation\Http\FormRequest;

class StoreFacebookPageConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(TenantPermission::IntegrationsManage) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page_id' => ['required', 'string', 'max:64'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'page_id.required' => __('Enter your Facebook Page ID.'),
        ];
    }

    /**
     * @return array{page_id: string}
     */
    public function connectionData(): array
    {
        return [
            'page_id' => $this->validated('page_id'),
        ];
    }
}
