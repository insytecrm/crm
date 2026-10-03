<?php

namespace App\Support\DataTable;

use App\Contracts\DataTableDefinition;
use App\Enums\ActivityFilter;
use App\Enums\ScheduledActivityStage;
use App\Enums\TaskFilter;
use Illuminate\Support\Str;

class TablePreferencesSupport
{
    public static function storageKey(string $tableKey): string
    {
        return "{$tableKey}_table";
    }

    /**
     * @return list<string>
     */
    public static function listingKeys(string $tableKey): array
    {
        return match ($tableKey) {
            'follow_ups', 'site_visits' => array_map(
                fn (ScheduledActivityStage $stage): string => $stage->value,
                ScheduledActivityStage::tabs(),
            ),
            'activities', 'activities_site_visits' => array_map(
                fn (ActivityFilter $filter): string => $filter->value,
                ActivityFilter::cases(),
            ),
            'tasks' => array_map(
                fn (TaskFilter $filter): string => $filter->value,
                TaskFilter::cases(),
            ),
            default => [],
        };
    }

    public static function usesListings(string $tableKey): bool
    {
        return self::listingKeys($tableKey) !== [];
    }

    public static function isListingKey(string $tableKey, string $listingKey): bool
    {
        return in_array($listingKey, self::listingKeys($tableKey), true);
    }

    public static function defaultListingKey(string $tableKey): ?string
    {
        return match ($tableKey) {
            'follow_ups', 'site_visits' => ScheduledActivityStage::Pending->value,
            'activities', 'activities_site_visits' => ActivityFilter::All->value,
            'tasks' => TaskFilter::All->value,
            default => null,
        };
    }

    /**
     * @return array{
     *     columns: array<string, bool>,
     *     custom_columns: list<array{
     *         key: string,
     *         label: string,
     *         type: string,
     *         options: list<string>,
     *         visible: bool,
     *     }>,
     * }
     */
    public static function defaults(DataTableDefinition $definition): array
    {
        return [
            'columns' => $definition->defaultColumns(),
            'custom_columns' => [],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $saved
     * @return array{
     *     columns: array<string, bool>,
     *     custom_columns: list<array{
     *         key: string,
     *         label: string,
     *         type: string,
     *         options: list<string>,
     *         visible: bool,
     *     }>,
     * }
     */
    public static function resolve(DataTableDefinition $definition, ?array $saved): array
    {
        $defaults = self::defaults($definition);

        if ($saved === null) {
            return $defaults;
        }

        return [
            'columns' => self::mergeBooleanMap(
                $defaults['columns'],
                is_array($saved['columns'] ?? null) ? $saved['columns'] : [],
                $definition->requiredColumns(),
            ),
            'custom_columns' => self::normalizeCustomColumns(
                is_array($saved['custom_columns'] ?? null) ? $saved['custom_columns'] : [],
            ),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $savedByListing
     * @return array{
     *     columns: array<string, bool>,
     *     custom_columns: list<array{
     *         key: string,
     *         label: string,
     *         type: string,
     *         options: list<string>,
     *         visible: bool,
     *     }>,
     * }
     */
    public static function resolveForListing(
        DataTableDefinition $definition,
        ?array $savedByListing,
        string $listingKey,
    ): array {
        $tableKey = $definition->key();

        if (! self::usesListings($tableKey)) {
            return self::resolve($definition, is_array($savedByListing) ? $savedByListing : null);
        }

        if ($savedByListing === null) {
            return self::defaults($definition);
        }

        if (self::isLegacySavedFormat($savedByListing)) {
            $defaultListingKey = self::defaultListingKey($tableKey);

            return $listingKey === $defaultListingKey
                ? self::resolve($definition, $savedByListing)
                : self::defaults($definition);
        }

        $saved = $savedByListing[$listingKey] ?? null;

        return self::resolveForListingKey(
            $definition,
            is_array($saved) ? $saved : null,
        );
    }

    /**
     * @param  array<string, mixed>|null  $saved
     * @return array{
     *     columns: array<string, bool>,
     *     custom_columns: list<array{
     *         key: string,
     *         label: string,
     *         type: string,
     *         options: list<string>,
     *         visible: bool,
     *     }>,
     * }
     */
    public static function resolveForListingKey(DataTableDefinition $definition, ?array $saved): array
    {
        return self::resolve($definition, $saved);
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
     * @return array<string, array{
     *     columns: array<string, bool>,
     *     custom_columns: list<array{
     *         key: string,
     *         label: string,
     *         type: string,
     *         options: list<string>,
     *         visible: bool,
     *     }>,
     * }>
     */
    public static function normalizeSavedByListing(DataTableDefinition $definition, ?array $saved): array
    {
        $tableKey = $definition->key();

        if (! self::usesListings($tableKey)) {
            return is_array($saved) ? [self::normalize($definition, $saved)] : [];
        }

        if ($saved === null) {
            return [];
        }

        if (self::isLegacySavedFormat($saved)) {
            $defaultListingKey = self::defaultListingKey($tableKey);

            return $defaultListingKey !== null
                ? [$defaultListingKey => self::normalize($definition, $saved)]
                : [];
        }

        $normalized = [];

        foreach (self::listingKeys($tableKey) as $listingKey) {
            if (! isset($saved[$listingKey]) || ! is_array($saved[$listingKey])) {
                continue;
            }

            $normalized[$listingKey] = self::normalize($definition, $saved[$listingKey]);
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{
     *     columns: array<string, bool>,
     *     custom_columns: list<array{
     *         key: string,
     *         label: string,
     *         type: string,
     *         options: list<string>,
     *         visible: bool,
     *     }>,
     * }
     */
    public static function normalize(DataTableDefinition $definition, array $input): array
    {
        $defaults = self::defaults($definition);

        return [
            'columns' => self::mergeBooleanMap(
                $defaults['columns'],
                is_array($input['columns'] ?? null) ? $input['columns'] : [],
                $definition->requiredColumns(),
            ),
            'custom_columns' => self::normalizeCustomColumns(
                is_array($input['custom_columns'] ?? null) ? $input['custom_columns'] : [],
            ),
        ];
    }

    /**
     * @param  array<string, bool>  $defaults
     * @param  array<string, mixed>  $overrides
     * @param  list<string>  $requiredColumns
     * @return array<string, bool>
     */
    private static function mergeBooleanMap(array $defaults, array $overrides, array $requiredColumns): array
    {
        $merged = [];

        foreach ($defaults as $key => $defaultValue) {
            $merged[$key] = array_key_exists($key, $overrides)
                ? (bool) $overrides[$key]
                : $defaultValue;
        }

        foreach ($requiredColumns as $requiredColumn) {
            $merged[$requiredColumn] = true;
        }

        return $merged;
    }

    /**
     * @param  list<array<string, mixed>>  $customColumns
     * @return list<array{
     *     key: string,
     *     label: string,
     *     type: string,
     *     options: list<string>,
     *     visible: bool,
     * }>
     */
    private static function normalizeCustomColumns(array $customColumns): array
    {
        $normalized = [];

        foreach ($customColumns as $column) {
            if (! is_array($column)) {
                continue;
            }

            $label = trim((string) ($column['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $key = trim((string) ($column['key'] ?? ''));

            if ($key === '') {
                $key = Str::slug($label, '_');
            }

            $type = ($column['type'] ?? 'text') === 'select' ? 'select' : 'text';
            $options = [];

            if ($type === 'select' && is_array($column['options'] ?? null)) {
                foreach ($column['options'] as $option) {
                    $optionLabel = trim((string) $option);

                    if ($optionLabel !== '') {
                        $options[] = $optionLabel;
                    }
                }
            }

            $normalized[] = [
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'options' => $options,
                'visible' => (bool) ($column['visible'] ?? true),
            ];
        }

        return $normalized;
    }
}
