<?php

namespace App\Support;

use App\Enums\LeadListingFilter;

class LeadTablePreferences
{
    public const string StorageKey = 'leads_table';

    /**
     * @return list<string>
     */
    public static function listingKeys(): array
    {
        return array_map(
            fn (LeadListingFilter $listing): string => $listing->value,
            LeadListingFilter::cases(),
        );
    }

    public static function isListingKey(string $key): bool
    {
        return in_array($key, self::listingKeys(), true);
    }

    /**
     * @return array{
     *     columns: array<string, bool>,
     *     actions: array<string, bool>,
     * }
     */
    public static function defaults(): array
    {
        return [
            'columns' => [
                'name' => true,
                'phone' => true,
                'source' => true,
                'requirement' => true,
                'assigned_to' => true,
                'status' => true,
                'next_follow_up' => true,
                'follow_ups_count' => false,
                'site_visits_count' => false,
                'last_activity' => true,
                'property_interest' => false,
                'booking_date' => false,
                'property_booked' => false,
                'created_at' => true,
                'actions' => true,
            ],
            'actions' => [
                'call' => true,
                'whatsapp' => true,
                'follow_up' => true,
                'site_visit' => true,
                'create_booking' => true,
            ],
        ];
    }

    /**
     * @return array{
     *     columns: array<string, bool>,
     *     actions: array<string, bool>,
     * }
     */
    public static function defaultsForListing(LeadListingFilter $listing): array
    {
        $defaults = self::defaults();

        if ($listing === LeadListingFilter::Converted) {
            $defaults['columns']['booking_date'] = true;
            $defaults['columns']['property_booked'] = true;
        }

        return $defaults;
    }

    /**
     * @param  array<string, mixed>|null  $savedByListing
     * @return array{
     *     columns: array<string, bool>,
     *     actions: array<string, bool>,
     * }
     */
    public static function resolveForListing(?array $savedByListing, LeadListingFilter $listing): array
    {
        if ($savedByListing === null) {
            return self::defaultsForListing($listing);
        }

        if (self::isLegacySavedFormat($savedByListing)) {
            return $listing === LeadListingFilter::All
                ? self::resolve($savedByListing)
                : self::defaultsForListing($listing);
        }

        $saved = $savedByListing[$listing->value] ?? null;

        return self::resolveForListingKey(is_array($saved) ? $saved : null, $listing);
    }

    /**
     * @param  array<string, mixed>|null  $saved
     * @return array{
     *     columns: array<string, bool>,
     *     actions: array<string, bool>,
     * }
     */
    public static function resolveForListingKey(?array $saved, LeadListingFilter $listing): array
    {
        $defaults = self::defaultsForListing($listing);

        if ($saved === null) {
            return $defaults;
        }

        return [
            'columns' => self::mergeBooleanMap($defaults['columns'], $saved['columns'] ?? [], requireName: true),
            'actions' => self::mergeBooleanMap($defaults['actions'], $saved['actions'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $saved
     * @return array<string, array{
     *     columns: array<string, bool>,
     *     actions: array<string, bool>,
     * }>
     */
    public static function normalizeSavedByListing(?array $saved): array
    {
        if ($saved === null) {
            return [];
        }

        if (self::isLegacySavedFormat($saved)) {
            return [
                LeadListingFilter::All->value => self::normalize($saved),
            ];
        }

        $normalized = [];

        foreach (self::listingKeys() as $listingKey) {
            if (! isset($saved[$listingKey]) || ! is_array($saved[$listingKey])) {
                continue;
            }

            $normalized[$listingKey] = self::normalize($saved[$listingKey]);
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>|null  $saved
     */
    public static function isLegacySavedFormat(?array $saved): bool
    {
        return is_array($saved) && isset($saved['columns']) && is_array($saved['columns']);
    }

    /**
     * @param  array<string, mixed>|null  $saved
     * @return array{
     *     columns: array<string, bool>,
     *     actions: array<string, bool>,
     * }
     */
    public static function resolve(?array $saved): array
    {
        $defaults = self::defaults();

        if ($saved === null) {
            return $defaults;
        }

        return [
            'columns' => self::mergeBooleanMap($defaults['columns'], $saved['columns'] ?? [], requireName: true),
            'actions' => self::mergeBooleanMap($defaults['actions'], $saved['actions'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{
     *     columns: array<string, bool>,
     *     actions: array<string, bool>,
     * }
     */
    public static function normalize(array $input): array
    {
        $defaults = self::defaults();

        return [
            'columns' => self::mergeBooleanMap($defaults['columns'], $input['columns'] ?? [], requireName: true),
            'actions' => self::mergeBooleanMap($defaults['actions'], $input['actions'] ?? []),
        ];
    }

    /**
     * @param  array<string, bool>  $defaults
     * @param  array<string, mixed>  $overrides
     * @return array<string, bool>
     */
    private static function mergeBooleanMap(array $defaults, array $overrides, bool $requireName = false): array
    {
        $merged = [];

        foreach ($defaults as $key => $defaultValue) {
            $merged[$key] = array_key_exists($key, $overrides)
                ? (bool) $overrides[$key]
                : $defaultValue;
        }

        if ($requireName) {
            $merged['name'] = true;
        }

        return $merged;
    }
}
