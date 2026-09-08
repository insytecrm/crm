<?php

namespace App\Support;

use App\Enums\LeadSource;
use App\Models\FacebookPageConnection;
use App\Models\GoogleSheetConnection;
use App\Models\Lead;
use App\Models\Property;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LeadRoutingSubSourceOptions
{
    /**
     * @return array<string, list<array{value: string, label: string}>>
     */
    public function all(): array
    {
        $options = [];

        foreach (LeadSource::cases() as $source) {
            $options[$source->value] = $this->forSource($source);
        }

        return $options;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function forSource(LeadSource|string $source): array
    {
        $sourceValue = $source instanceof LeadSource ? $source->value : $source;
        $sourceEnum = $source instanceof LeadSource ? $source : LeadSource::tryFrom($sourceValue);

        $labels = collect();

        if ($sourceEnum === LeadSource::Facebook) {
            $labels = $labels->merge($this->facebookOptions());
        }

        if ($sourceEnum === LeadSource::GoogleSheets) {
            $labels = $labels->merge($this->googleSheetOptions());
        }

        if ($sourceEnum === LeadSource::Microsite) {
            $labels = $labels->merge($this->micrositeOptions());
        }

        $labels = $labels->merge($this->existingLeadSubSources($sourceValue));

        return $labels
            ->map(fn (string $label): string => trim($label))
            ->filter()
            ->unique(fn (string $label): string => Str::lower($label))
            ->sort(fn (string $a, string $b): int => strcasecmp($a, $b))
            ->values()
            ->map(fn (string $label): array => [
                'value' => $label,
                'label' => $label,
            ])
            ->all();
    }

    /**
     * @return Collection<int, string>
     */
    private function facebookOptions(): Collection
    {
        $labels = collect();

        FacebookPageConnection::query()
            ->get(['page_name', 'campaigns', 'lead_forms'])
            ->each(function (FacebookPageConnection $connection) use ($labels): void {
                foreach ($connection->campaigns ?? [] as $campaign) {
                    if (! is_array($campaign)) {
                        continue;
                    }

                    $name = trim((string) ($campaign['name'] ?? ''));

                    if ($name !== '') {
                        $labels->push($name);
                    }
                }

                foreach ($connection->lead_forms ?? [] as $form) {
                    if (! is_array($form)) {
                        continue;
                    }

                    $name = trim((string) ($form['name'] ?? ''));

                    if ($name !== '') {
                        $labels->push($name);
                    }
                }

                $pageName = trim((string) ($connection->page_name ?? ''));

                if ($pageName !== '') {
                    $labels->push($pageName);
                }
            });

        return $labels;
    }

    /**
     * @return Collection<int, string>
     */
    private function googleSheetOptions(): Collection
    {
        return GoogleSheetConnection::query()
            ->get(['name', 'sheet_title'])
            ->map(function (GoogleSheetConnection $connection): ?string {
                $name = trim((string) ($connection->name ?: $connection->sheet_title ?: ''));

                return $name !== '' ? $name : null;
            })
            ->filter()
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function micrositeOptions(): Collection
    {
        return Property::query()
            ->whereNotNull('project_name')
            ->where('project_name', '!=', '')
            ->orderBy('project_name')
            ->pluck('project_name')
            ->filter()
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function existingLeadSubSources(string $source): Collection
    {
        return Lead::query()
            ->where('source', $source)
            ->whereNotNull('sub_source')
            ->where('sub_source', '!=', '')
            ->distinct()
            ->orderBy('sub_source')
            ->pluck('sub_source')
            ->filter()
            ->values();
    }
}
