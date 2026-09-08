<?php

namespace App\Http\Requests\Tenant;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class StoreMicrositeEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $settings = $this->leadFormSettings();

        return [
            'name' => [
                $settings['show_name'] ? ($settings['require_name'] ? 'required' : 'nullable') : 'prohibited',
                'string',
                'max:255',
            ],
            'phone' => [
                $settings['show_phone'] ? ($settings['require_phone'] ? 'required' : 'nullable') : 'prohibited',
                'string',
                'max:30',
            ],
            'email' => [
                $settings['show_email'] ? ($settings['require_email'] ? 'required' : 'nullable') : 'prohibited',
                'email',
                'max:255',
            ],
            'configuration' => [
                $settings['show_configuration'] ? ($settings['require_configuration'] ? 'required' : 'nullable') : 'prohibited',
                'string',
                'max:255',
            ],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }

    /**
     * @return array{
     *     show_name: bool,
     *     show_phone: bool,
     *     show_email: bool,
     *     show_configuration: bool,
     *     require_name: bool,
     *     require_phone: bool,
     *     require_email: bool,
     *     require_configuration: bool
     * }
     */
    public function leadFormSettings(): array
    {
        $slug = (string) $this->route('slug');

        $property = Property::query()
            ->with('microsite')
            ->where('microsite_slug', $slug)
            ->where('microsite_enabled', true)
            ->first();

        return $property?->microsite?->leadFormSettings() ?? [
            'show_name' => true,
            'show_phone' => true,
            'show_email' => true,
            'show_configuration' => true,
            'require_name' => true,
            'require_phone' => true,
            'require_email' => false,
            'require_configuration' => false,
        ];
    }
}
