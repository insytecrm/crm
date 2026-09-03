<?php

namespace App\Http\Requests\Tenant;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
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
            'property_id' => ['required', 'exists:properties,id'],
            'configuration_index' => ['required', 'integer', 'min:0'],
            'unit_number' => ['required', 'string', 'max:50'],
            'agreement_value' => ['required', 'integer', 'min:1'],
            'booking_date' => ['required', 'date'],
            'lead_id' => ['required', 'exists:leads,id', Rule::unique('bookings', 'lead_id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lead_id.unique' => __('This lead already has a booking.'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $property = Property::query()->find($this->integer('property_id'));
            $configurationIndex = $this->integer('configuration_index');
            $configurations = $property?->configurations ?? [];

            if ($property === null || ! array_key_exists($configurationIndex, $configurations)) {
                $validator->errors()->add('configuration_index', __('Select a valid configuration for this property.'));
            }
        });
    }

    /**
     * @return array{name: string, carpet_area_sqft: ?int, price: ?int, unit_count: ?int}
     */
    public function configuration(): array
    {
        $property = Property::query()->findOrFail($this->integer('property_id'));
        $configurations = $property->configurations ?? [];

        return $configurations[$this->integer('configuration_index')];
    }
}
