<?php

namespace App\Http\Requests\Tenant;

use App\Enums\MicrositeFont;
use App\Enums\MicrositeSection;
use App\Enums\MicrositeTheme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyMicrositeContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $leadForm = $this->input('lead_form', []);

        if (! is_array($leadForm)) {
            $leadForm = [];
        }

        foreach ([
            'show_name',
            'show_phone',
            'show_email',
            'show_configuration',
            'require_name',
            'require_phone',
            'require_email',
            'require_configuration',
        ] as $flag) {
            $leadForm[$flag] = $this->boolean('lead_form.'.$flag);
        }

        $this->merge([
            'lead_form' => $leadForm,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $sectionKeys = MicrositeSection::keys();

        return [
            'theme_preset' => ['required', Rule::enum(MicrositeTheme::class)],
            'theme_accent' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font_primary' => ['required', Rule::enum(MicrositeFont::class)],
            'font_secondary' => ['required', Rule::enum(MicrositeFont::class)],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'lead_form' => ['nullable', 'array'],
            'lead_form.title' => ['nullable', 'string', 'max:80'],
            'lead_form.subtitle' => ['nullable', 'string', 'max:160'],
            'lead_form.submit_label' => ['nullable', 'string', 'max:80'],
            'lead_form.success_message' => ['nullable', 'string', 'max:255'],
            'lead_form.show_name' => ['nullable', 'boolean'],
            'lead_form.show_phone' => ['nullable', 'boolean'],
            'lead_form.show_email' => ['nullable', 'boolean'],
            'lead_form.show_configuration' => ['nullable', 'boolean'],
            'lead_form.require_name' => ['nullable', 'boolean'],
            'lead_form.require_phone' => ['nullable', 'boolean'],
            'lead_form.require_email' => ['nullable', 'boolean'],
            'lead_form.require_configuration' => ['nullable', 'boolean'],
            'lead_form.label_name' => ['nullable', 'string', 'max:80'],
            'lead_form.label_phone' => ['nullable', 'string', 'max:80'],
            'lead_form.label_email' => ['nullable', 'string', 'max:80'],
            'lead_form.label_configuration' => ['nullable', 'string', 'max:80'],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['array'],
            ...$this->sectionRules($sectionKeys),
            'logo_image' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
            'hero_image' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
            'about_image' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
            'developer_image' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
            'location_image' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
            'gallery' => ['nullable', 'array', 'max:20'],
            'gallery.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_hero' => ['nullable', 'boolean'],
            'remove_about' => ['nullable', 'boolean'],
            'remove_developer' => ['nullable', 'boolean'],
            'remove_location' => ['nullable', 'boolean'],
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['string', 'max:36'],
        ];
    }

    /**
     * @param  list<string>  $sectionKeys
     * @return array<string, mixed>
     */
    private function sectionRules(array $sectionKeys): array
    {
        $rules = [];

        foreach ($sectionKeys as $key) {
            $rules["sections.{$key}.headline"] = ['nullable', 'string', 'max:80'];
            $rules["sections.{$key}.subheadline"] = ['nullable', 'string', 'max:160'];
            $rules["sections.{$key}.body"] = ['nullable', 'string', 'max:600'];
            $rules["sections.{$key}.button_label"] = ['nullable', 'string', 'max:80'];
            $rules["sections.{$key}.video_url"] = ['nullable', 'string', 'max:500', 'url'];
            $rules["sections.{$key}.map_url"] = ['nullable', 'string', 'max:1000', 'url'];
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $validated = $this->validated();

        foreach (['remove_logo', 'remove_hero', 'remove_about', 'remove_developer', 'remove_location'] as $flag) {
            $validated[$flag] = $this->boolean($flag);
        }

        return $validated;
    }
}
