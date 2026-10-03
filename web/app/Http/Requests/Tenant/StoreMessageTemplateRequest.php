<?php

namespace App\Http\Requests\Tenant;

use App\Enums\MessageTemplateChannel;
use App\Enums\TenantPermission;
use App\Support\TemplateVariableCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMessageTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(TenantPermission::AutomationsManage) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'channel' => ['required', Rule::enum(MessageTemplateChannel::class)],
            'subject' => ['nullable', 'required_if:channel,'.MessageTemplateChannel::Email->value, 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('Please name this template.'),
            'channel.required' => __('Choose WhatsApp, Email, or SMS.'),
            'subject.required_if' => __('Add a subject for email templates.'),
            'body.required' => __('Write the template message.'),
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

                $unknown = array_values(array_unique([
                    ...TemplateVariableCatalog::unknownKeysIn((string) $this->input('subject', '')),
                    ...TemplateVariableCatalog::unknownKeysIn((string) $this->input('body', '')),
                ]));

                if ($unknown === []) {
                    return;
                }

                $validator->errors()->add(
                    'body',
                    __('Unknown variable :token. Choose a variable from the list.', [
                        'token' => TemplateVariableCatalog::token($unknown[0]),
                    ]),
                );
            },
        ];
    }

    /**
     * @return array{name: string, channel: MessageTemplateChannel, subject: ?string, body: string, is_active: bool}
     */
    public function templateData(): array
    {
        $validated = $this->validated();
        $channel = MessageTemplateChannel::from($validated['channel']);

        return [
            'name' => $validated['name'],
            'channel' => $channel,
            'subject' => $channel->requiresSubject() ? ($validated['subject'] ?? null) : null,
            'body' => $validated['body'],
            'is_active' => $this->boolean('is_active'),
        ];
    }
}
