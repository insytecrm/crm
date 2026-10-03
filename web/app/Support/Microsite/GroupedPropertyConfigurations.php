<?php

namespace App\Support\Microsite;

class GroupedPropertyConfigurations
{
    /**
     * @param  array<int, mixed>|null  $configurations
     * @return list<array{
     *     name: string,
     *     variant_count: int,
     *     carpet_from: ?int,
     *     carpet_to: ?int,
     *     carpet_label: ?string,
     *     price_from: ?int,
     *     price_to: ?int,
     *     price_label: ?string,
     *     variants: list<array{name: string, carpet_area_sqft: ?int, carpet_label: ?string, price: ?int, price_label: ?string, unit_count: ?int}>
     * }>
     */
    public function group(?array $configurations): array
    {
        $rows = collect($configurations ?? [])
            ->filter(fn (mixed $row): bool => is_array($row))
            ->map(function (array $row): array {
                $carpet = isset($row['carpet_area_sqft']) && $row['carpet_area_sqft'] !== '' && $row['carpet_area_sqft'] !== null
                    ? (int) $row['carpet_area_sqft']
                    : null;
                $price = isset($row['price']) && $row['price'] !== '' && $row['price'] !== null
                    ? (int) $row['price']
                    : null;
                $unitCount = isset($row['unit_count']) && $row['unit_count'] !== '' && $row['unit_count'] !== null
                    ? (int) $row['unit_count']
                    : null;
                $name = trim((string) ($row['name'] ?? ''));

                return [
                    'name' => $name !== '' ? $name : __('Configuration'),
                    'carpet_area_sqft' => $carpet,
                    'carpet_label' => $carpet !== null ? number_format($carpet).' '.__('sq.ft') : null,
                    'price' => $price,
                    'price_label' => MicrositeMoney::rupees($price),
                    'unit_count' => $unitCount,
                ];
            })
            ->values();

        if ($rows->isEmpty()) {
            return [];
        }

        return $rows
            ->groupBy(fn (array $row): string => mb_strtolower($row['name']))
            ->map(function ($group) {
                $items = $group->values();
                $carpets = $items->pluck('carpet_area_sqft')->filter(fn ($value): bool => $value !== null);
                $prices = $items->pluck('price')->filter(fn ($value): bool => $value !== null);
                $name = (string) $items->first()['name'];

                $carpetFrom = $carpets->min();
                $carpetTo = $carpets->max();
                $priceFrom = $prices->min();
                $priceTo = $prices->max();

                return [
                    'name' => $name,
                    'variant_count' => $items->count(),
                    'carpet_from' => $carpetFrom !== null ? (int) $carpetFrom : null,
                    'carpet_to' => $carpetTo !== null ? (int) $carpetTo : null,
                    'carpet_label' => $this->areaRange(
                        $carpetFrom !== null ? (int) $carpetFrom : null,
                        $carpetTo !== null ? (int) $carpetTo : null,
                    ),
                    'price_from' => $priceFrom !== null ? (int) $priceFrom : null,
                    'price_to' => $priceTo !== null ? (int) $priceTo : null,
                    'price_label' => MicrositeMoney::range(
                        $priceFrom !== null ? (int) $priceFrom : null,
                        $priceTo !== null ? (int) $priceTo : null,
                    ),
                    'variants' => $items->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function areaRange(?int $from, ?int $to): ?string
    {
        if ($from !== null && $to !== null) {
            if ($from === $to) {
                return number_format($from).' '.__('sq.ft');
            }

            return number_format($from).' – '.number_format($to).' '.__('sq.ft');
        }

        if ($from !== null) {
            return __('From').' '.number_format($from).' '.__('sq.ft');
        }

        if ($to !== null) {
            return __('Up to').' '.number_format($to).' '.__('sq.ft');
        }

        return null;
    }
}
