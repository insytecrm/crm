<?php

namespace App\Support\Platform;

use App\Enums\PlanCapability;
use App\Enums\PlanFeature;
use App\Enums\PlanLimitKey;
use App\Enums\PlanPack;
use App\Models\PlanPackPreset;

class PlanDefinitionCatalog
{
    /**
     * @return list<string>
     */
    public static function defaultPresetKeys(PlanFeature $feature, PlanPack $pack): array
    {
        $capabilities = match ($feature) {
            PlanFeature::Crm => match ($pack) {
                PlanPack::Basic => [],
                PlanPack::Advanced => [
                    PlanCapability::CrmImport,
                    PlanCapability::CrmExport,
                    PlanCapability::CrmDuplicates,
                    PlanCapability::CrmDocuments,
                ],
                default => [],
            },
            PlanFeature::InsyteAi => match ($pack) {
                PlanPack::Basic => [
                    PlanCapability::AiChat,
                    PlanCapability::AiSearchLeads,
                    PlanCapability::AiListToday,
                ],
                PlanPack::Advanced => PlanCapability::forFeature(PlanFeature::InsyteAi),
                default => [],
            },
            PlanFeature::Automations => match ($pack) {
                PlanPack::Basic => [
                    PlanCapability::AutomationsActionCreateTask,
                    PlanCapability::AutomationsActionAddNote,
                    PlanCapability::AutomationsActionChangeStatus,
                    PlanCapability::AutomationsActionScheduleFollowUp,
                    PlanCapability::AutomationsTemplates,
                ],
                PlanPack::Advanced => PlanCapability::forFeature(PlanFeature::Automations),
                default => [],
            },
            PlanFeature::Reports => match ($pack) {
                PlanPack::Basic => [],
                PlanPack::Advanced => [
                    PlanCapability::ReportsAnalytics,
                    PlanCapability::ReportsExport,
                    PlanCapability::ReportsPrint,
                ],
                default => [],
            },
            default => [],
        };

        return array_map(
            fn (PlanCapability $capability): string => $capability->value,
            $capabilities,
        );
    }

    /**
     * @param  array<string, bool>  $features
     * @param  array<string, string>  $packs
     * @param  list<string>  $customCapabilities
     * @return list<string>
     */
    public static function resolveCapabilityKeys(array $features, array $packs, array $customCapabilities): array
    {
        $resolved = [];

        foreach (PlanFeature::cases() as $feature) {
            if (! ($features[$feature->value] ?? false)) {
                continue;
            }

            $pack = PlanPack::tryFrom((string) ($packs[$feature->value] ?? '')) ?? PlanPack::Advanced;

            if ($pack === PlanPack::Off) {
                continue;
            }

            if ($pack === PlanPack::Custom || $feature === PlanFeature::Integrations) {
                foreach ($customCapabilities as $key) {
                    $capability = PlanCapability::tryFrom($key);

                    if ($capability?->feature() === $feature) {
                        $resolved[] = $capability->value;
                    }
                }

                continue;
            }

            if ($feature->isPackable() && in_array($pack, [PlanPack::Basic, PlanPack::Advanced], true)) {
                $presetKeys = PlanPackPreset::capabilityKeys($feature, $pack);

                if ($presetKeys === []) {
                    $presetKeys = self::defaultPresetKeys($feature, $pack);
                }

                foreach ($presetKeys as $key) {
                    $resolved[] = $key;
                }

                continue;
            }

            foreach (PlanCapability::forFeature($feature) as $capability) {
                $resolved[] = $capability->value;
            }
        }

        return array_values(array_unique($resolved));
    }

    /**
     * @return array<string, bool>
     */
    public static function defaultFeatures(array $enabled): array
    {
        $features = [];

        foreach (PlanFeature::cases() as $feature) {
            $features[$feature->value] = in_array($feature, $enabled, true);
        }

        return $features;
    }

    /**
     * @param  array<string, PlanPack>|array<string, string>  $packs
     * @return array<string, string>
     */
    public static function defaultPacks(array $packs): array
    {
        $values = [];

        foreach (PlanFeature::cases() as $feature) {
            $pack = $packs[$feature->value] ?? PlanPack::Off;
            $values[$feature->value] = $pack instanceof PlanPack ? $pack->value : (string) $pack;
        }

        return $values;
    }

    /**
     * @param  array<string, int|null>  $limits
     * @return array<string, int|null>
     */
    public static function defaultLimits(array $limits = []): array
    {
        $values = [];

        foreach (PlanLimitKey::cases() as $limit) {
            $values[$limit->value] = $limits[$limit->value] ?? null;
        }

        return $values;
    }

    /**
     * @return list<PlanFeature>
     */
    public static function packableFeatures(): array
    {
        return array_values(array_filter(
            PlanFeature::cases(),
            fn (PlanFeature $feature): bool => $feature->isPackable(),
        ));
    }
}
