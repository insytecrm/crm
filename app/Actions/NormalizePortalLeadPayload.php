<?php

namespace App\Actions;

use App\Enums\PropertyPortal;
use App\Enums\PropertyType;
use Illuminate\Support\Arr;

class NormalizePortalLeadPayload
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function handle(array $payload, PropertyPortal $portal): array
    {
        $normalized = [
            'name' => $this->firstString($payload, [
                'name',
                'Name',
                'lead_name',
                'customer_name',
                'full_name',
                'FullName',
            ]) ?? __(':portal enquiry', ['portal' => $portal->label()]),
            'phone' => $this->firstString($payload, [
                'phone',
                'Phone',
                'mobile',
                'Mobile',
                'phone_number',
                'mobile_number',
            ]),
            'email' => $this->firstString($payload, [
                'email',
                'Email',
                'email_id',
                'EmailId',
            ]),
            'location' => $this->firstString($payload, [
                'location',
                'Location',
                'city',
                'City',
                'locality',
            ]),
            'configuration' => $this->firstString($payload, [
                'configuration',
                'bhk',
                'BHK',
            ]),
            'property_type' => $this->resolvePropertyType($payload),
            'source' => $portal->leadSource()->value,
        ];

        return array_filter(
            $normalized,
            static fn (mixed $value): bool => $value !== null && $value !== '',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $keys
     */
    private function firstString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = Arr::get($payload, $key);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }

            if (is_numeric($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolvePropertyType(array $payload): ?string
    {
        $raw = $this->firstString($payload, [
            'property_type',
            'PropertyType',
            'propertyType',
        ]);

        return PropertyType::tryFromMixed($raw)?->value;
    }
}
