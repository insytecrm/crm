<?php

namespace App\Http\Requests\Tenant;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MergeLeadsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()?->hasPermission('leads.update');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'primary_lead_id' => ['required', 'integer', Rule::exists('leads', 'id')],
            'duplicate_lead_ids' => ['required', 'array', 'min:1'],
            'duplicate_lead_ids.*' => ['required', 'integer', Rule::exists('leads', 'id')],
        ];
    }

    public function primaryLead(): Lead
    {
        return Lead::query()->findOrFail($this->integer('primary_lead_id'));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'duplicate_lead_ids.required' => __('Select at least one duplicate lead to merge.'),
            'duplicate_lead_ids.min' => __('Select at least one duplicate lead to merge.'),
        ];
    }
}
