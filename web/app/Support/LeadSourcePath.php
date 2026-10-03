<?php

namespace App\Support;

class LeadSourcePath
{
    public const Separator = ' › ';

    /**
     * @param  list<array{key: string, id?: string|null, label: string}>  $segments
     * @return array{sub_source: string|null, source_context: array{segments: list<array{key: string, id: string|null, label: string}>}|null}
     */
    public static function fromSegments(array $segments): array
    {
        $normalized = [];

        foreach ($segments as $segment) {
            $label = trim((string) ($segment['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $normalized[] = [
                'key' => (string) ($segment['key'] ?? 'segment'),
                'id' => isset($segment['id']) && filled($segment['id']) ? (string) $segment['id'] : null,
                'label' => $label,
            ];
        }

        if ($normalized === []) {
            return [
                'sub_source' => null,
                'source_context' => null,
            ];
        }

        return [
            'sub_source' => implode(self::Separator, array_column($normalized, 'label')),
            'source_context' => [
                'segments' => $normalized,
            ],
        ];
    }

    /**
     * @param  array{segments?: list<array{key?: string, id?: string|null, label?: string}>}|null  $context
     */
    public static function breadcrumb(?array $context): ?string
    {
        $segments = $context['segments'] ?? null;

        if (! is_array($segments) || $segments === []) {
            return null;
        }

        $labels = [];

        foreach ($segments as $segment) {
            if (! is_array($segment)) {
                continue;
            }

            $label = trim((string) ($segment['label'] ?? ''));

            if ($label !== '') {
                $labels[] = $label;
            }
        }

        return $labels === [] ? null : implode(self::Separator, $labels);
    }
}
