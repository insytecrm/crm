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
                ['key' => 'lead.interested_in', 'label' => __('Interested in'), 'sample' => 'Riverfront Residences'],
                ['key' => 'lead.created_at', 'label' => __('Created date'), 'sample' => '1 Sep 2026'],
                ['key' => 'lead.last_activity', 'label' => __('Last activity'), 'sample' => '2 hours ago'],
                ['key' => 'lead.closed_at', 'label' => __('Closed date'), 'sample' => '10 Sep 2026'],
                ['key' => 'lead.closing_reason', 'label' => __('Closing reason'), 'sample' => 'Not interested'],
                ['key' => 'lead.closing_notes', 'label' => __('Closing notes'), 'sample' => 'Budget mismatch'],
                ['key' => 'lead.next_follow_up', 'label' => __('Next follow-up'), 'sample' => '12 Sep, 4:00 PM'],
                ['key' => 'lead.next_follow_up_date', 'label' => __('Next follow-up date'), 'sample' => '12 Sep 2026'],
                ['key' => 'lead.next_follow_up_time', 'label' => __('Next follow-up time'), 'sample' => '4:00 PM'],
                ['key' => 'lead.upcoming_site_visit', 'label' => __('Upcoming site visit'), 'sample' => '14 Sep, 11:00 AM'],
                ['key' => 'lead.upcoming_site_visit_date', 'label' => __('Upcoming site visit date'), 'sample' => '14 Sep 2026'],
                ['key' => 'lead.upcoming_site_visit_time', 'label' => __('Upcoming site visit time'), 'sample' => '11:00 AM'],
            ]),
            self::group('assigned', __('Assigned user'), [
                ['key' => 'assigned.name', 'label' => __('Name'), 'sample' => 'Priya Nair'],
                ['key' => 'assigned.email', 'label' => __('Email'), 'sample' => 'priya@acme.test'],
                ['key' => 'assigned.phone', 'label' => __('Phone'), 'sample' => '+91 91234 56789'],
            ]),
            self::group('user', __('Current user'), [
                ['key' => 'user.name', 'label' => __('Name'), 'sample' => 'Amit Desai'],
                ['key' => 'user.email', 'label' => __('Email'), 'sample' => 'amit@acme.test'],
                ['key' => 'user.phone', 'label' => __('Phone'), 'sample' => '+91 90000 11111'],
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
            self::scheduledEventGroup('follow_up', __('Follow-up'), 'upcoming', self::upcomingEventVariables('follow_up.upcoming', false)),
            self::scheduledEventGroup('follow_up', __('Follow-up'), 'previous', self::completedEventVariables('follow_up.previous', false)),
            self::scheduledEventGroup('follow_up', __('Follow-up'), 'completed', self::completedEventVariables('follow_up.completed', false)),
            self::scheduledEventGroup('site_visit', __('Site visit'), 'upcoming', self::upcomingEventVariables('site_visit.upcoming', true)),
            self::scheduledEventGroup('site_visit', __('Site visit'), 'previous', self::completedEventVariables('site_visit.previous', true)),
            self::scheduledEventGroup('site_visit', __('Site visit'), 'completed', self::completedEventVariables('site_visit.completed', true)),
            self::group('booking', __('Booking'), [
                ['key' => 'booking.property', 'label' => __('Property'), 'sample' => 'Riverfront Residences'],
                ['key' => 'booking.unit_number', 'label' => __('Unit number'), 'sample' => 'A-1204'],
                ['key' => 'booking.configuration', 'label' => __('Configuration'), 'sample' => '3 BHK'],
                ['key' => 'booking.agreement_value', 'label' => __('Agreement value'), 'sample' => '₹1.15 Cr'],
                ['key' => 'booking.booking_date', 'label' => __('Booking date'), 'sample' => '6 Sep 2026'],
                ['key' => 'booking.agreement_date', 'label' => __('Agreement date'), 'sample' => '20 Sep 2026'],
                ['key' => 'booking.invoice_number', 'label' => __('Invoice number'), 'sample' => 'INV-1042'],
                ['key' => 'booking.invoice_date', 'label' => __('Invoice date'), 'sample' => '25 Sep 2026'],
                ['key' => 'booking.payout_amount', 'label' => __('Payout amount'), 'sample' => '₹1.15 L'],
                ['key' => 'booking.payout_percent', 'label' => __('Payout percent'), 'sample' => '1%'],
            ]),
        ];
    }

    /**
     * @return list<array{key: string, label: string, sample: string}>
     */
    private static function upcomingEventVariables(string $prefix, bool $isSiteVisit): array
    {
        $variables = [
            ['key' => "{$prefix}.activity", 'label' => __('Activity'), 'sample' => $isSiteVisit ? '1st Site Visit · Fresh Visit' : '2nd Follow-up'],
            ['key' => "{$prefix}.date", 'label' => __('Date'), 'sample' => '14 Sep 2026'],
            ['key' => "{$prefix}.time", 'label' => __('Time'), 'sample' => '11:00 AM'],
            ['key' => "{$prefix}.state", 'label' => __('State'), 'sample' => 'Scheduled'],
            ['key' => "{$prefix}.priority", 'label' => __('Priority'), 'sample' => 'High'],
            ['key' => "{$prefix}.notes", 'label' => __('Notes'), 'sample' => 'Confirm parking availability.'],
        ];

        if ($isSiteVisit) {
            array_splice($variables, 3, 0, [
                ['key' => "{$prefix}.property", 'label' => __('Property'), 'sample' => 'Riverfront Residences'],
                ['key' => "{$prefix}.visit_type", 'label' => __('Visit type'), 'sample' => 'Fresh Visit'],
            ]);
        }

        return $variables;
    }

    /**
     * @return list<array{key: string, label: string, sample: string}>
     */
    private static function completedEventVariables(string $prefix, bool $isSiteVisit): array
    {
        $variables = [
            ['key' => "{$prefix}.activity", 'label' => __('Activity'), 'sample' => $isSiteVisit ? '1st Site Visit · Fresh Visit' : 'Initial Contact'],
            ['key' => "{$prefix}.date", 'label' => __('Date'), 'sample' => '10 Sep 2026'],
            ['key' => "{$prefix}.time", 'label' => __('Time'), 'sample' => '3:30 PM'],
            ['key' => "{$prefix}.priority", 'label' => __('Priority'), 'sample' => 'Normal'],
            ['key' => "{$prefix}.notes", 'label' => __('Notes'), 'sample' => 'Customer asked for floor plan.'],
            ['key' => "{$prefix}.outcome", 'label' => __('Outcome'), 'sample' => $isSiteVisit ? 'Interested' : 'Connected'],
            ['key' => "{$prefix}.next_step", 'label' => __('Next step'), 'sample' => $isSiteVisit ? 'Schedule site visit' : 'Follow-up call'],
        ];

        if ($isSiteVisit) {
            array_splice($variables, 3, 0, [
                ['key' => "{$prefix}.property", 'label' => __('Property'), 'sample' => 'Riverfront Residences'],
                ['key' => "{$prefix}.visit_type", 'label' => __('Visit type'), 'sample' => 'Fresh Visit'],
                ['key' => "{$prefix}.attended", 'label' => __('Attended'), 'sample' => 'Yes'],
            ]);
        } else {
            array_splice($variables, 5, 0, [
                ['key' => "{$prefix}.method", 'label' => __('Method'), 'sample' => 'Call'],
            ]);
        }

        return $variables;
    }

    /**
     * @param  list<array{key: string, label: string, sample: string}>  $variables
     * @return array{key: string, label: string, variables: list<array{key: string, label: string, token: string, sample: string}>}
     */
    private static function scheduledEventGroup(string $sectionKey, string $sectionLabel, string $context, array $variables): array
    {
        return self::group(
            "{$sectionKey}_{$context}",
            __(':section · :context', [
                'section' => $sectionLabel,
                'context' => match ($context) {
                    'upcoming' => __('Upcoming'),
                    'previous' => __('Previous'),
                    'completed' => __('Completed'),
                    default => ucfirst($context),
                },
            ]),
            $variables,
        );
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
