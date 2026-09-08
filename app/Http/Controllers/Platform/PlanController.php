<?php

namespace App\Http\Controllers\Platform;

use App\Actions\ArchivePlan;
use App\Actions\DuplicatePlan;
use App\Actions\UpdatePlan;
use App\Actions\UpdatePlanPackPresets;
use App\Enums\PlanCapability;
use App\Enums\PlanFeature;
use App\Enums\PlanLimitKey;
use App\Enums\PlanPack;
use App\Enums\PlanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\DuplicatePlanRequest;
use App\Http\Requests\Platform\UpdatePlanPackPresetsRequest;
use App\Http\Requests\Platform\UpdatePlanRequest;
use App\Models\Plan;
use App\Models\PlanPackPreset;
use App\Models\Tenant;
use App\Support\Platform\PlanDefinitionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::query()
            ->orderByRaw('case when status = ? then 0 else 1 end', [PlanStatus::Active->value])
            ->orderBy('price_monthly')
            ->orderBy('id')
            ->get()
            ->map(function (Plan $plan): Plan {
                $plan->setAttribute('partners_count', $plan->activePartnersCount());

                return $plan;
            });

        return view('platform.plans.index', [
            'plans' => $plans,
        ]);
    }

    public function show(Plan $plan): View
    {
        return view('platform.plans.show', [
            'plan' => $plan,
            'shell' => $this->shell($plan, 'overview'),
            'includedFeatures' => $this->includedFeatures($plan),
            'includedCapabilities' => $this->includedCapabilities($plan),
        ]);
    }

    public function features(Plan $plan): View
    {
        return view('platform.plans.features', [
            'plan' => $plan,
            'shell' => $this->shell($plan, 'features'),
            'featureStates' => $this->featureStates($plan),
            'presets' => $this->presetEditor(),
        ]);
    }

    public function limits(Plan $plan): View
    {
        return view('platform.plans.limits', [
            'plan' => $plan,
            'shell' => $this->shell($plan, 'limits'),
            'limitRows' => $this->limitRows($plan),
        ]);
    }

    public function partners(Plan $plan): View
    {
        $partners = Tenant::query()
            ->wherePlanKey($plan->key)
            ->latest()
            ->orderByDesc('id')
            ->get();

        return view('platform.plans.partners', [
            'plan' => $plan,
            'shell' => $this->shell($plan, 'partners'),
            'partners' => $partners,
            'partnersCount' => $partners->count(),
        ]);
    }

    public function edit(Plan $plan): View
    {
        return view('platform.plans.edit', [
            'plan' => $plan,
            'statuses' => PlanStatus::cases(),
            'features' => PlanFeature::modules(),
            'integrations' => PlanCapability::forFeature(PlanFeature::Integrations),
            'packableFeatures' => PlanDefinitionCatalog::packableFeatures(),
            'limits' => PlanLimitKey::cases(),
            'packs' => [PlanPack::Basic, PlanPack::Advanced, PlanPack::Custom],
            'capabilitiesByFeature' => $this->capabilitiesByFeature(),
            'presets' => $this->presetEditor(),
            'partnersCount' => $plan->activePartnersCount(),
        ]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan, UpdatePlan $updatePlan): RedirectResponse
    {
        $updatePlan->handle($plan, $request->validated());

        return redirect()
            ->route('platform.plans.show', $plan)
            ->with('status', __('Plan updated. Changes may affect Channel Partners currently using this plan.'));
    }

    public function archive(Plan $plan, ArchivePlan $archivePlan): RedirectResponse
    {
        $archivePlan->handle($plan);

        return redirect()
            ->route('platform.plans')
            ->with('status', __('Plan archived. Existing Channel Partners stay on their current subscription.'));
    }

    public function duplicate(Plan $plan): View
    {
        return view('platform.plans.duplicate', [
            'plan' => $plan,
        ]);
    }

    public function storeDuplicate(DuplicatePlanRequest $request, Plan $plan, DuplicatePlan $duplicatePlan): RedirectResponse
    {
        $copy = $duplicatePlan->handle($plan, $request->validated());

        return redirect()
            ->route('platform.plans.edit', $copy)
            ->with('status', __('Plan duplicated. Review and save any changes.'));
    }

    public function updatePresets(UpdatePlanPackPresetsRequest $request, UpdatePlanPackPresets $updatePlanPackPresets): RedirectResponse
    {
        $updatePlanPackPresets->handle($request->validated('presets'));

        return back()->with('status', __('Basic and Advanced packs updated. Plans using those packs will pick up the new meaning.'));
    }

    /**
     * @return array{
     *     name: string,
     *     status: string,
     *     tabs: list<array{key: string, label: string, href: string, active: bool}>,
     *     partners_count: int
     * }
     */
    private function shell(Plan $plan, string $activeTab): array
    {
        $tabs = [
            ['key' => 'overview', 'label' => __('Overview'), 'route' => 'platform.plans.show'],
            ['key' => 'features', 'label' => __('Features'), 'route' => 'platform.plans.features'],
            ['key' => 'limits', 'label' => __('Limits'), 'route' => 'platform.plans.limits'],
            ['key' => 'partners', 'label' => __('Partners'), 'route' => 'platform.plans.partners'],
        ];

        return [
            'name' => $plan->name,
            'status' => $plan->status->value,
            'tabs' => collect($tabs)
                ->map(fn (array $tab): array => [
                    'key' => $tab['key'],
                    'label' => $tab['label'],
                    'href' => route($tab['route'], $plan),
                    'active' => $activeTab === $tab['key'],
                ])
                ->all(),
            'partners_count' => $plan->activePartnersCount(),
        ];
    }

    /**
     * @return list<array{label: string, included: bool}>
     */
    private function includedFeatures(Plan $plan): array
    {
        return collect(PlanFeature::modules())
            ->map(fn (PlanFeature $feature): array => [
                'label' => $feature->label(),
                'included' => $plan->hasFeature($feature),
            ])
            ->all();
    }

    /**
     * @return list<array{feature: string, label: string, included: bool, pack: string|null, capabilities: list<array{label: string, included: bool}>}>
     */
    private function featureStates(Plan $plan): array
    {
        $resolved = $plan->resolvedCapabilityKeys();

        $rows = collect(PlanFeature::modules())
            ->map(function (PlanFeature $feature) use ($plan, $resolved): array {
                $pack = $plan->packFor($feature);

                return [
                    'feature' => $feature->value,
                    'label' => $feature->label(),
                    'included' => $plan->hasFeature($feature),
                    'pack' => $plan->hasFeature($feature) && $feature->isPackable() ? $pack->label() : null,
                    'capabilities' => collect(PlanCapability::forFeature($feature))
                        ->reject(fn (PlanCapability $capability): bool => $capability->feature() === PlanFeature::Integrations)
                        ->map(fn (PlanCapability $capability): array => [
                            'label' => $capability->label(),
                            'included' => in_array($capability->value, $resolved, true),
                            'coming_soon' => ! $capability->isShipped(),
                        ])
                        ->all(),
                ];
            })
            ->all();

        $rows[] = [
            'feature' => PlanFeature::Integrations->value,
            'label' => PlanFeature::Integrations->label(),
            'included' => $plan->hasFeature(PlanFeature::Integrations),
            'pack' => null,
            'capabilities' => collect(PlanCapability::forFeature(PlanFeature::Integrations))
                ->map(fn (PlanCapability $capability): array => [
                    'label' => $capability->label(),
                    'included' => in_array($capability->value, $resolved, true),
                    'coming_soon' => ! $capability->isShipped(),
                ])
                ->all(),
        ];

        return $rows;
    }

    /**
     * @return list<array{label: string}>
     */
    private function includedCapabilities(Plan $plan): array
    {
        return collect($plan->resolvedCapabilityKeys())
            ->map(fn (string $key): ?PlanCapability => PlanCapability::tryFrom($key))
            ->filter()
            ->map(fn (PlanCapability $capability): array => [
                'label' => $capability->label(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, label: string, value: int|null, unit: string|null}>
     */
    private function limitRows(Plan $plan): array
    {
        return collect(PlanLimitKey::cases())
            ->map(fn (PlanLimitKey $limit): array => [
                'key' => $limit->value,
                'label' => $limit->label(),
                'value' => $plan->limitFor($limit->value),
                'unit' => $limit->unit(),
            ])
            ->all();
    }

    /**
     * @return array<string, list<array{value: string, label: string, shipped: bool}>>
     */
    private function capabilitiesByFeature(): array
    {
        $grouped = [];

        foreach (PlanFeature::cases() as $feature) {
            $capabilities = PlanCapability::forFeature($feature);

            if ($capabilities === []) {
                continue;
            }

            $grouped[$feature->value] = collect($capabilities)
                ->map(fn (PlanCapability $capability): array => [
                    'value' => $capability->value,
                    'label' => $capability->label(),
                    'shipped' => $capability->isShipped(),
                ])
                ->all();
        }

        return $grouped;
    }

    /**
     * @return array<string, array{label: string, basic: list<string>, advanced: list<string>, options: list<array{value: string, label: string}>}>
     */
    private function presetEditor(): array
    {
        $editor = [];

        foreach (PlanDefinitionCatalog::packableFeatures() as $feature) {
            $editor[$feature->value] = [
                'label' => $feature->label(),
                'basic' => PlanPackPreset::capabilityKeys($feature, PlanPack::Basic)
                    ?: PlanDefinitionCatalog::defaultPresetKeys($feature, PlanPack::Basic),
                'advanced' => PlanPackPreset::capabilityKeys($feature, PlanPack::Advanced)
                    ?: PlanDefinitionCatalog::defaultPresetKeys($feature, PlanPack::Advanced),
                'options' => collect(PlanCapability::forFeature($feature))
                    ->map(fn (PlanCapability $capability): array => [
                        'value' => $capability->value,
                        'label' => $capability->label(),
                    ])
                    ->all(),
            ];
        }

        return $editor;
    }
}
