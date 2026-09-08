<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TenantPermission;
use Illuminate\Foundation\Http\FormRequest;

class StoreGoogleSheetConnectionRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'spreadsheet_url' => ['required', 'string', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('Name this Google Sheet integration.'),
            'spreadsheet_url.required' => __('Paste the Google Sheet URL.'),
        ];
    }

    /**
     * @return array{name: string, spreadsheet_url: string}
     */
    public function connectionData(): array
    {
        $validated = $this->validated();

        return [
            'name' => $validated['name'],
            'spreadsheet_url' => $validated['spreadsheet_url'],
        ];
    }
}
