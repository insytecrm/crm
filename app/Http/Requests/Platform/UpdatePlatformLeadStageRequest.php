<?php

namespace App\Http\Requests\Platform;

use App\Enums\PlatformLeadStage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformLeadStageRequest extends FormRequest
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
        return [
            'stage' => ['required', Rule::enum(PlatformLeadStage::class)],
            'demo_date' => ['nullable', 'date', 'required_if:stage,demo_scheduled'],
            'demo_time' => ['nullable', 'date_format:H:i', 'required_if:stage,demo_scheduled'],
        ];
    }
}
