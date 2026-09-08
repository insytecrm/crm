<?php

namespace App\Http\Controllers\Platform;

use App\Actions\CreatePlan;
use App\Enums\PlanCapability;
use App\Enums\PlanFeature;
use App\Enums\PlanLimitKey;
use App\Enums\PlanPack;
use App\Enums\PlanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StorePlanWizardBasicRequest;
use App\Http\Requests\Platform\StorePlanWizardFeaturesRequest;
use App\Http\Requests\Platform\StorePlanWizardLimitsRequest;
use App\Http\Requests\Platform\StorePlanWizardPricingRequest;
use App\Models\PlanPackPreset;
use App\Support\Platform\PlanDefinitionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanWizardController extends Controller
{
    public const SessionKey = 'plan_wizard';

    public function create(): RedirectResponse
    {
        session()->forget(self::SessionKey);

        return redirect()->route('platform.plans.wizard.basic');
    }

    public function basic(): View
    {
        return view('platform.plans.wizard.basic', $this->wizardView(1, [
            'statuses' => PlanStatus::cases(),
        ]));
    }

    public function storeBasic(StorePlanWizardBasicRequest $request): RedirectResponse
    {
        $this->mergeDraft($request->validated());

        return redirect()->route('platform.plans.wizard.pricing');
    }

    public function pricing(): RedirectResponse|View
    {
        if (! $this->hasDraftKeys(['name', 'status'])) {
            return redirect()->route('platform.plans.wizard.basic');
        }

        return view('platform.plans.wizard.pricing', $this->wizardView(2));
    }

    public function storePricing(StorePlanWizardPricingRequest $request): RedirectResponse
    {
        $this->mergeDraft($request->validated());

        return redirect()->route('platform.plans.wizard.features');
    }

    public function features(): RedirectResponse|View
    {
        if (! $this->hasDraftKeys(['name', 'status', 'price_monthly', 'price_annual'])) {
            return redirect()->route('platform.plans.wizard.pricing');
        }

        return view('platform.plans.wizard.features', $this->wizardView(3, [
            'modules' => PlanFeature::modules(),
            'integrations' => PlanCapability::forFeature(PlanFeature::Integrations),
            'packableFeatures' => PlanDefinitionCatalog::packableFeatures(),
            'packs' => [PlanPack::Basic, PlanPack::Advanced, PlanPack::Custom],
            'capabilitiesByFeature' => $this->capabilitiesByFeature(),
            'presets' => $this->presetEditor(),
        ]));
    }

    public function storeFeatures(StorePlanWizardFeaturesRequest $request): RedirectResponse
    {
        $this->mergeDraft($request->validated());

        return redirect()->route('platform.plans.wizard.limits');
    }

    public function limits(): RedirectResponse|View
    {
        if (! $this->hasDraftKeys(['name', 'status', 'price_monthly', 'price_annual', 'features'])) {
            return redirect()->route('platform.plans.wizard.features');
        }

        return view('platform.plans.wizard.limits', $this->wizardView(4, [
            'limits' => PlanLimitKey::cases(),
        ]));
    }

    public function storeLimits(StorePlanWizardLimitsRequest $request): RedirectResponse
    {
        $this->mergeDraft($request->validated());

        return redirect()->route('platform.plans.wizard.review');
    }

    public function review(): RedirectResponse|View
    {
        if (! $this->hasDraftKeys(['name', 'status', 'price_monthly', 'price_annual', 'features', 'limits'])) {
            return redirect()->route('platform.plans.wizard.limits');
        }

        $draft = $this->draft();

        return view('platform.plans.wizard.review', $this->wizardView(5, [
            'includedModules' => collect(PlanFeature::modules())
                ->filter(fn (PlanFeature $feature): bool => (bool) ($draft['features'][$feature->value] ?? false))
                ->map(fn (PlanFeature $feature): string => $feature->label())
                ->values()
                ->all(),
            'includedIntegrations' => collect($draft['capabilities'] ?? [])
                ->map(fn (string $key): ?PlanCapability => PlanCapability::tryFrom($key))
                ->filter()
                ->filter(fn (PlanCapability $capability): bool => $capability->feature() === PlanFeature::Integrations)
                ->map(fn (PlanCapability $capability): string => $capability->label())
                ->values()
                ->all(),
            'limitRows' => collect(PlanLimitKey::cases())
                ->map(fn (PlanLimitKey $limit): array => [
                    'label' => $limit->label(),
                    'value' => $draft['limits'][$limit->value] ?? null,
                    'unit' => $limit->unit(),
                ])
                ->all(),
        ]));
    }

    public function store(CreatePlan $createPlan): RedirectResponse
    {
        if (! $this->hasDraftKeys(['name', 'status', 'price_monthly', 'price_annual', 'features', 'limits'])) {
            return redirect()->route('platform.plans.wizard.basic');
        }

        $plan = $createPlan->handle($this->draft());

        session()->forget(self::SessionKey);

        return redirect()
            ->route('platform.plans.show', $plan)
            ->with('status', __('Plan created.'));
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function wizardView(int $step, array $extra = []): array
    {
        return array_merge([
            'step' => $step,
            'draft' => $this->draft(),
        ], $extra);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function mergeDraft(array $data): void
    {
        session()->put(self::SessionKey, array_merge($this->draft(), $data));
    }

    /**
     * @return array<string, mixed>
     */
    private function draft(): array
    {
        $draft = session(self::SessionKey, []);

        return is_array($draft) ? $draft : [];
    }

    /**
     * @param  list<string>  $keys
     */
    private function hasDraftKeys(array $keys): bool
    {
        $draft = $this->draft();

        foreach ($keys as $key) {
            if (! array_key_exists($key, $draft) || $draft[$key] === null || $draft[$key] === '') {
                return false;
            }
        }

        return true;
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
