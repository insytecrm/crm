<?php

namespace App\Support;

class TemplateVariableCatalog
{
    /**
     * @return list<array{key: string, label: string, variables: list<array{key: string, label: string, token: string, sample: string}>}>
     */
    public static function groups(): array
    {
        return [
            self::group('lead', __('Lead'), [
                ['key' => 'lead.name', 'label' => __('Name'), 'sample' => 'Rahul Sharma'],
                ['key' => 'lead.phone', 'label' => __('Phone'), 'sample' => '+91 98765 43210'],
                ['key' => 'lead.email', 'label' => __('Email'), 'sample' => 'rahul@example.com'],
                ['key' => 'lead.status', 'label' => __('Status'), 'sample' => 'Follow-up'],
                ['key' => 'lead.source', 'label' => __('Source'), 'sample' => 'Referral'],
                ['key' => 'lead.sub_source', 'label' => __('Sub-source'), 'sample' => 'Acme FB Page'],
                ['key' => 'lead.budget', 'label' => __('Budget'), 'sample' => '80L – 1.2Cr'],
                ['key' => 'lead.location', 'label' => __('Location'), 'sample' => 'Koregaon Park'],
                ['key' => 'lead.property_type', 'label' => __('Property type'), 'sample' => 'Apartment'],
                ['key' => 'lead.configuration', 'label' => __('Configuration'), 'sample' => '3 BHK'],
                ['key' => 'lead.next_action', 'label' => __('Next action'), 'sample' => 'Share brochure'],
                ['key' => 'lead.score', 'label' => __('Lead score'), 'sample' => '82'],
                ['key' => 'lead.next_follow_up', 'label' => __('Next follow-up'), 'sample' => '12 Sep, 4:00 PM'],
                ['key' => 'lead.upcoming_site_visit', 'label' => __('Upcoming site visit'), 'sample' => '14 Sep, 11:00 AM'],
            ]),
            self::group('assigned', __('Assigned user'), [
                ['key' => 'assigned.name', 'label' => __('Name'), 'sample' => 'Priya Nair'],
                ['key' => 'assigned.email', 'label' => __('Email'), 'sample' => 'priya@acme.test'],
            ]),
            self::group('user', __('Current user'), [
                ['key' => 'user.name', 'label' => __('Name'), 'sample' => 'Amit Desai'],
                ['key' => 'user.email', 'label' => __('Email'), 'sample' => 'amit@acme.test'],
            ]),
            self::group('company', __('Company'), [
                ['key' => 'company.name', 'label' => __('Name'), 'sample' => 'Acme Realty'],
                ['key' => 'company.email', 'label' => __('Email'), 'sample' => 'hello@acme.test'],
            ]),
            self::group('property', __('Property'), [
                ['key' => 'property.project_name', 'label' => __('Project name'), 'sample' => 'Riverfront Residences'],
                ['key' => 'property.developer_name', 'label' => __('Developer'), 'sample' => 'Skyline Developers'],
                ['key' => 'property.location', 'label' => __('Location'), 'sample' => 'Baner'],
                ['key' => 'property.rera_number', 'label' => __('RERA number'), 'sample' => 'P52100012345'],
                ['key' => 'property.type', 'label' => __('Type'), 'sample' => 'Apartment'],
                ['key' => 'property.status', 'label' => __('Status'), 'sample' => 'Under construction'],
                ['key' => 'property.possession_date', 'label' => __('Possession date'), 'sample' => 'Dec 2027'],
                ['key' => 'property.price_from', 'label' => __('Price from'), 'sample' => '₹85 L'],
                ['key' => 'property.price_to', 'label' => __('Price to'), 'sample' => '₹1.4 Cr'],
                ['key' => 'property.carpet_area', 'label' => __('Carpet area'), 'sample' => '980–1,240 sq.ft'],
                ['key' => 'property.sourcing_manager_name', 'label' => __('Sourcing manager'), 'sample' => 'Neha Kulkarni'],
                ['key' => 'property.sourcing_manager_contact', 'label' => __('Sourcing manager contact'), 'sample' => '+91 99887 76655'],
                ['key' => 'property.amenities', 'label' => __('Amenities'), 'sample' => 'Clubhouse, pool, gym'],
                ['key' => 'property.microsite_url', 'label' => __('Microsite URL'), 'sample' => 'https://acme.test/projects/riverfront'],
            ]),
            self::group('booking', __('Booking'), [
                ['key' => 'booking.unit_number', 'label' => __('Unit number'), 'sample' => 'A-1204'],
                ['key' => 'booking.configuration', 'label' => __('Configuration'), 'sample' => '3 BHK'],
                ['key' => 'booking.agreement_value', 'label' => __('Agreement value'), 'sample' => '₹1.15 Cr'],
                ['key' => 'booking.booking_date', 'label' => __('Booking date'), 'sample' => '6 Sep 2026'],
                ['key' => 'booking.agreement_date', 'label' => __('Agreement date'), 'sample' => '20 Sep 2026'],
                ['key' => 'booking.invoice_number', 'label' => __('Invoice number'), 'sample' => 'INV-1042'],
            ]),
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return collect(self::groups())
            ->flatMap(fn (array $group): array => array_column($group['variables'], 'key'))
            ->values()
            ->all();
    }

    public static function has(string $key): bool
    {
        return in_array($key, self::keys(), true);
    }

    public static function token(string $key): string
    {
        return '{{'.$key.'}}';
    }

    /**
     * @return list<string>
     */
    public static function keysIn(string $text): array
    {
        preg_match_all('/\{\{([a-z0-9_.]+)\}\}/', $text, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    /**
     * @return list<string>
     */
    public static function unknownKeysIn(string $text): array
    {
        return array_values(array_filter(
            self::keysIn($text),
            fn (string $key): bool => ! self::has($key),
        ));
    }

    /**
     * @return array<string, string>
     */
    public static function sampleValues(): array
    {
        $samples = [];

        foreach (self::groups() as $group) {
            foreach ($group['variables'] as $variable) {
                $samples[$variable['key']] = $variable['sample'];
            }
        }

        return $samples;
    }

    /**
     * @param  list<array{key: string, label: string, sample: string}>  $variables
     * @return array{key: string, label: string, variables: list<array{key: string, label: string, token: string, sample: string}>}
     */
    private static function group(string $key, string $label, array $variables): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'variables' => array_map(
                fn (array $variable): array => [
                    'key' => $variable['key'],
                    'label' => $variable['label'],
                    'token' => self::token($variable['key']),
                    'sample' => $variable['sample'],
                ],
                $variables,
            ),
        ];
    }
}
