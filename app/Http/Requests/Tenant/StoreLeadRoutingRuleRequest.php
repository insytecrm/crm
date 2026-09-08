<?php

namespace App\Http\Requests\Tenant;

use App\Enums\LeadRoutingDistribution;
use App\Enums\LeadSource;
use App\Enums\TenantPermission;
use App\Models\LeadRoutingRule;
use App\Models\SalesTeam;
use App\Support\LeadRoutingSubSourceOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLeadRoutingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(TenantPermission::TeamsManage) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'source' => ['required', Rule::enum(LeadSource::class)],
            'sub_source' => ['nullable', 'string', 'max:255'],
            'sales_team_id' => ['required', 'integer', 'exists:sales_teams,id'],
            'distribution' => ['required', Rule::enum(LeadRoutingDistribution::class)],
            'is_active' => ['sometimes', 'boolean'],
            'members' => ['required', 'array', 'min:1'],
            'members.*.user_id' => ['required', 'integer', 'exists:users,id', 'distinct'],
            'members.*.weight' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->assertUniqueSource($validator);
                $this->assertSubSourceAllowed($validator);
                $this->assertTeamIsActive($validator);
            },
        ];
    }

    /**
     * @return array{
     *     source: string,
     *     sub_source: ?string,
     *     sales_team_id: int,
     *     distribution: string,
     *     is_active: bool,
     *     members: list<array{user_id: int, weight?: int}>
     * }
     */
    public function ruleData(): array
    {
        $validated = $this->validated();

        return [
            'source' => $validated['source'],
            'sub_source' => $validated['sub_source'] ?? null,
            'sales_team_id' => (int) $validated['sales_team_id'],
            'distribution' => $validated['distribution'],
            'is_active' => $this->boolean('is_active', true),
            'members' => array_values($validated['members']),
        ];
    }

    protected function assertUniqueSource(Validator $validator): void
    {
        $source = (string) $this->input('source');
        $subSource = filled($this->input('sub_source')) ? trim((string) $this->input('sub_source')) : null;

        $exists = LeadRoutingRule::query()
            ->where('source', $source)
            ->when(
                $subSource === null,
                fn ($query) => $query->whereNull('sub_source'),
                fn ($query) => $query->where('sub_source', $subSource),
            )
            ->when(
                $this->route('routingRule') instanceof LeadRoutingRule,
                fn ($query) => $query->whereKeyNot($this->route('routingRule')->id),
            )
            ->exists();

        if ($exists) {
            $validator->errors()->add(
                'source',
                __('A routing rule already exists for this source and sub-source.'),
            );
        }
    }

    protected function assertSubSourceAllowed(Validator $validator): void
    {
        $subSource = filled($this->input('sub_source')) ? trim((string) $this->input('sub_source')) : null;

        if ($subSource === null) {
            return;
        }

        $source = (string) $this->input('source');
        $options = app(LeadRoutingSubSourceOptions::class)->forSource($source);
        $allowed = collect($options)->pluck('value')->all();

        if ($this->route('routingRule') instanceof LeadRoutingRule) {
            $current = $this->route('routingRule')->sub_source;

            if (filled($current)) {
                $allowed[] = (string) $current;
            }
        }

        $matches = collect($allowed)->contains(
            fn (string $value): bool => strcasecmp($value, $subSource) === 0,
        );

        if (! $matches) {
            $validator->errors()->add(
                'sub_source',
                __('Choose a sub-source from the list for this source.'),
            );
        }
    }

    protected function assertTeamIsActive(Validator $validator): void
    {
        $team = SalesTeam::query()->find($this->input('sales_team_id'));

        if ($team instanceof SalesTeam && ! $team->isActive()) {
            $validator->errors()->add(
                'sales_team_id',
                __('Choose an active sales team.'),
            );
        }
    }
}
