<?php

namespace App\Actions;

use App\Enums\LeadRoutingDistribution;
use App\Models\LeadRoutingRule;
use App\Models\SalesTeam;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveLeadRoutingRule
{
    /**
     * @param  array{
     *     source: string,
     *     sub_source?: ?string,
     *     sales_team_id: int,
     *     distribution: string,
     *     is_active?: bool,
     *     members: list<array{user_id: int, weight?: int}>
     * }  $data
     */
    public function handle(array $data, ?LeadRoutingRule $rule = null, ?User $actor = null): LeadRoutingRule
    {
        $team = SalesTeam::query()->findOrFail($data['sales_team_id']);
        $memberIds = collect($data['members'])->pluck('user_id')->map(fn ($id): int => (int) $id)->unique()->values();

        if ($memberIds->isEmpty()) {
            throw ValidationException::withMessages([
                'members' => __('Select at least one team member for routing.'),
            ]);
        }

        $teamMemberIds = $team->members()->pluck('users.id')->map(fn ($id): int => (int) $id);

        if ($memberIds->diff($teamMemberIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'members' => __('Every selected member must belong to the chosen team.'),
            ]);
        }

        $distribution = LeadRoutingDistribution::from($data['distribution']);
        $subSource = filled($data['sub_source'] ?? null) ? trim((string) $data['sub_source']) : null;

        return DB::transaction(function () use ($data, $rule, $actor, $distribution, $subSource): LeadRoutingRule {
            $payload = [
                'source' => $data['source'],
                'sub_source' => $subSource,
                'sales_team_id' => (int) $data['sales_team_id'],
                'distribution' => $distribution,
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
            ];

            if ($rule === null) {
                $rule = LeadRoutingRule::query()->create([
                    ...$payload,
                    'created_by_id' => $actor?->id,
                    'distribution_cursor' => 0,
                ]);
            } else {
                $rule->update($payload);
            }

            $sync = [];

            foreach ($data['members'] as $member) {
                $userId = (int) $member['user_id'];
                $weight = max(1, (int) ($member['weight'] ?? 1));
                $sync[$userId] = ['weight' => $distribution->value === 'custom' ? $weight : 1];
            }

            $rule->members()->sync($sync);

            return $rule->fresh(['team', 'members']) ?? $rule;
        });
    }
}
