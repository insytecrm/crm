<?php

namespace App\Http\Controllers\Platform;

use App\Actions\AddPlatformLeadNote;
use App\Actions\CreatePlatformLead;
use App\Actions\UpdatePlatformLead;
use App\Actions\UpdatePlatformLeadStage;
use App\Contracts\ChannelPartnerProfileData;
use App\Enums\PlatformLeadSource;
use App\Enums\PlatformLeadStage;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StorePlatformLeadNoteRequest;
use App\Http\Requests\Platform\StorePlatformLeadRequest;
use App\Http\Requests\Platform\UpdatePlatformLeadNextActionRequest;
use App\Http\Requests\Platform\UpdatePlatformLeadRequest;
use App\Http\Requests\Platform\UpdatePlatformLeadStageRequest;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Models\PlatformLead;
use App\Models\PlatformLeadActivity;
use App\Models\User;
use App\Queries\PlatformLeadStatistics;
use App\Support\Platform\QuotationPricing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PlatformLeadController extends Controller
{
    public function index(Request $request, PlatformLeadStatistics $statistics): View
    {
        $search = trim((string) $request->string('search'));
        $stage = $request->string('stage')->toString() ?: 'all';
        $source = $request->string('source')->toString() ?: 'all';
        $ownerId = $request->integer('owner_id') ?: null;
        $accountStatus = $request->string('account_status')->toString() ?: 'all';

        $query = PlatformLead::query()
            ->with(['owner', 'tenant.partnerSubscriptions'])
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('company_name', 'like', '%'.$search.'%')
                    ->orWhere('contact_person', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if ($stage !== 'all' && PlatformLeadStage::tryFrom($stage) !== null) {
            $query->where('stage', $stage);
        }

        if ($source !== 'all' && PlatformLeadSource::tryFrom($source) !== null) {
            $query->where('source', $source);
        }

        if ($ownerId !== null) {
            $query->where('owner_id', $ownerId);
        }

        $this->applyAccountStatusFilter($query, $accountStatus);

        return view('platform.leads.index', array_merge($this->quotationModalContext($request), [
            'leads' => $query->paginate(20)->withQueryString(),
            'summary' => $statistics->summary(),
            'filters' => [
                'search' => $search,
                'stage' => $stage,
                'source' => $source,
                'owner_id' => $ownerId,
                'account_status' => $accountStatus,
            ],
            'stageOptions' => $this->stageFilterOptions(),
            'sourceOptions' => $this->sourceFilterOptions(),
            'accountStatusOptions' => $this->accountStatusFilterOptions(),
            'owners' => $this->ownerOptions(),
            'stages' => PlatformLeadStage::orderedCases(),
            'openQuotationModal' => $request->boolean('quote') || old('_quotation_wizard') === '1',
            'openNoteModal' => filled(old('body')) && filled(old('_lead_note_id')),
            'openAddLeadModal' => $this->shouldOpenAddLeadModal($request),
            'sources' => PlatformLeadSource::cases(),
        ]));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('platform.leads', ['add' => 1]);
    }

    public function store(StorePlatformLeadRequest $request, CreatePlatformLead $action): RedirectResponse
    {
        $lead = $action->handle($request->validated(), $request->user());

        return redirect()
            ->route('platform.leads.show', $lead)
            ->with('status', __('Lead created.'));
    }

    public function show(
        PlatformLead $lead,
        ChannelPartnerProfileData $profile,
        Request $request,
    ): View {
        $lead->load(['owner', 'creator', 'notes.user', 'activities.user', 'quotations.plan', 'tenant']);

        $accountOverview = null;
        $retentionSnapshot = null;

        if ($lead->hasLinkedAccount() && $lead->tenant !== null) {
            $accountOverview = $profile->overview($lead->tenant);

            if (in_array($lead->stage, [PlatformLeadStage::ClientLive, PlatformLeadStage::Retention], true)) {
                $subscription = $profile->subscription($lead->tenant);
                $nextBilling = collect($subscription['details'])
                    ->firstWhere('label', __('Next Billing'))['value'] ?? '—';

                $retentionSnapshot = [
                    'last_login' => $lead->tenant->updated_at?->diffForHumans() ?? '—',
                    'renewal' => $nextBilling,
                    'health' => count($accountOverview['attention']) > 0
                        ? __('Needs attention')
                        : __('Good'),
                ];
            }
        }

        $validationErrors = $request->session()->get('errors');

        return view('platform.leads.show', array_merge($this->quotationModalContext($request), [
            'lead' => $lead,
            'stages' => PlatformLeadStage::orderedCases(),
            'sources' => PlatformLeadSource::cases(),
            'owners' => $this->ownerOptions(),
            'accountOverview' => $accountOverview,
            'retentionSnapshot' => $retentionSnapshot,
            'activityGroups' => $this->groupActivities($lead),
            'openQuotationModal' => $request->boolean('quote') || old('_quotation_wizard') === '1',
            'openNoteModal' => $request->boolean('note') || filled(old('body')),
            'editing' => $request->boolean('edit') || ($validationErrors && $validationErrors->hasAny([
                'company_name', 'contact_person', 'email', 'phone', 'location', 'source', 'owner_id', 'stage',
            ])),
        ]));
    }

    public function update(UpdatePlatformLeadRequest $request, PlatformLead $lead, UpdatePlatformLead $action): RedirectResponse
    {
        $action->handle($lead, $request->validated(), $request->user());

        return redirect()
            ->route('platform.leads.show', $lead)
            ->with('status', __('Lead updated.'))
            ->withFragment('lead-information');
    }

    public function updateStage(
        UpdatePlatformLeadStageRequest $request,
        PlatformLead $lead,
        UpdatePlatformLeadStage $action,
    ): RedirectResponse {
        $data = $request->validated();
        $stage = PlatformLeadStage::from($data['stage']);

        $extras = [];
        if (isset($data['demo_date'])) {
            $extras['demo_date'] = $data['demo_date'];
        }
        if (isset($data['demo_time'])) {
            $extras['demo_time'] = $data['demo_time'];
        }

        $action->handle($lead, $stage, $request->user(), $extras);

        return redirect()
            ->back()
            ->with('status', __('Stage updated.'));
    }

    public function storeNote(
        StorePlatformLeadNoteRequest $request,
        PlatformLead $lead,
        AddPlatformLeadNote $action,
    ): RedirectResponse {
        $action->handle($lead, $request->validated('body'), $request->user());

        return redirect()
            ->back()
            ->with('status', __('Note added.'));
    }

    public function updateNextAction(
        UpdatePlatformLeadNextActionRequest $request,
        PlatformLead $lead,
    ): RedirectResponse {
        $date = $request->validated('next_action_at');
        $time = $request->validated('next_action_time');
        $at = null;

        if ($date !== null && $date !== '') {
            $at = Carbon::parse($date.' '.($time ?: '00:00'));
        }

        $lead->update([
            'next_action_label' => $request->validated('next_action_label'),
            'next_action_at' => $at,
        ]);

        return redirect()
            ->back()
            ->with('status', __('Next action updated.'));
    }

    /**
     * @return list<array{label: string, items: Collection<int, PlatformLeadActivity>}>
     */
    private function groupActivities(PlatformLead $lead): array
    {
        $timezone = config('app.timezone');
        $today = now()->timezone($timezone)->toDateString();
        $yesterday = now()->timezone($timezone)->subDay()->toDateString();

        $groups = [];
        foreach ($lead->activities as $activity) {
            $date = $activity->created_at?->timezone($timezone)->toDateString() ?? $today;
            $label = match ($date) {
                $today => __('Today'),
                $yesterday => __('Yesterday'),
                default => $activity->created_at?->timezone($timezone)->format('d M') ?? __('Earlier'),
            };

            if (! isset($groups[$label])) {
                $groups[$label] = collect();
            }

            $groups[$label]->push($activity);
        }

        return collect($groups)
            ->map(fn ($items, $label): array => ['label' => $label, 'items' => $items])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function stageFilterOptions(): array
    {
        return [
            ['value' => 'all', 'label' => __('All stages')],
            ...collect(PlatformLeadStage::orderedCases())
                ->map(fn (PlatformLeadStage $stage): array => [
                    'value' => $stage->value,
                    'label' => $stage->label(),
                ])
                ->all(),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function accountStatusFilterOptions(): array
    {
        return [
            ['value' => 'all', 'label' => __('All account statuses')],
            ['value' => 'none', 'label' => __('No account')],
            ['value' => TenantStatus::Active->value, 'label' => __('Active')],
            ['value' => SubscriptionStatus::Trial->value, 'label' => __('Trial')],
            ['value' => SubscriptionStatus::PastDue->value, 'label' => __('Past Due')],
            ['value' => TenantStatus::Suspended->value, 'label' => __('Suspended')],
        ];
    }

    private function shouldOpenAddLeadModal(Request $request): bool
    {
        if ($request->boolean('add')) {
            return true;
        }

        return filter_var(old('_add_lead_modal', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param  Builder<PlatformLead>  $query
     */
    private function applyAccountStatusFilter(Builder $query, string $accountStatus): void
    {
        if ($accountStatus === 'all') {
            return;
        }

        if ($accountStatus === 'none') {
            $query->whereNull('tenant_id');

            return;
        }

        $query->whereNotNull('tenant_id');

        if ($accountStatus === TenantStatus::Active->value) {
            $query->whereHas('tenant', fn ($builder) => $builder->where('status', TenantStatus::Active));

            return;
        }

        if ($accountStatus === TenantStatus::Suspended->value) {
            $query->whereHas('tenant', fn ($builder) => $builder->where('status', TenantStatus::Suspended));

            return;
        }

        if (in_array($accountStatus, [SubscriptionStatus::Trial->value, SubscriptionStatus::PastDue->value], true)) {
            $subscriptionStatus = $accountStatus === SubscriptionStatus::Trial->value
                ? SubscriptionStatus::Trial
                : SubscriptionStatus::PastDue;

            $tenantIds = PartnerSubscription::query()
                ->where('status', $subscriptionStatus)
                ->pluck('tenant_id');

            $query->whereIn('tenant_id', $tenantIds);
        }
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function sourceFilterOptions(): array
    {
        return [
            ['value' => 'all', 'label' => __('All sources')],
            ...collect(PlatformLeadSource::cases())
                ->map(fn (PlatformLeadSource $source): array => [
                    'value' => $source->value,
                    'label' => $source->label(),
                ])
                ->all(),
        ];
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function ownerOptions(): array
    {
        return User::query()
            ->where('is_super_admin', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function quotationModalContext(Request $request): array
    {
        return [
            'quotationPlans' => Plan::query()
                ->active()
                ->orderBy('price_monthly')
                ->get()
                ->map(fn (Plan $plan): array => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'price_monthly' => (int) $plan->price_monthly,
                    'price_annual' => (int) $plan->price_annual,
                    'trial_enabled' => (bool) $plan->trial_enabled,
                    'trial_days' => $plan->trial_enabled ? (int) $plan->trial_days : null,
                ])
                ->all(),
            'defaultTaxRate' => QuotationPricing::DefaultTaxRate,
            'leadSearchOptions' => PlatformLead::quotationSelectOptions(),
        ];
    }
}
