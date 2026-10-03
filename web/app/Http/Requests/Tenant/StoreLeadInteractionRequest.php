<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TenantPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadInteractionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->hasPermission(TenantPermission::LeadsUpdate);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'interaction_type' => ['required', Rule::in(['call', 'whatsapp', 'meeting', 'note'])],
            'body' => ['nullable', 'string', 'max:2000', 'required_if:interaction_type,note'],
            'next_follow_up_at' => ['nullable', 'date'],
            'next_follow_up_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
