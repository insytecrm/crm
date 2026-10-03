<?php

namespace App\Http\Requests\Tenant;

use App\Enums\MessageTemplateChannel;
use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SendLeadWhatsAppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'template_id' => [
                'nullable',
                'integer',
                Rule::exists('message_templates', 'id')
                    ->where('channel', MessageTemplateChannel::WhatsApp->value)
                    ->where('is_active', true),
            ],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'template_id.exists' => __('Please choose a WhatsApp template.'),
            'message.required' => __('Please write a message to send.'),
        ];
    }

    /**
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $lead = $this->route('lead');

                if (! $lead instanceof Lead || $lead->whatsAppUrl() === null) {
                    $validator->errors()->add(
                        'message',
                        __('This lead does not have a WhatsApp number.'),
                    );
                }
            },
        ];
    }
}
