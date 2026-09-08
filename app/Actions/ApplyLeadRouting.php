<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadRoutingRule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApplyLeadRouting
{
    public function handle(?string $source, ?string $subSource = null): ?int
    {
        if (! filled($source)) {
            return null;
        }

        $rule = $this->matchRule((string) $source, filled($subSource) ? (string) $subSource : null);

        if ($rule === null) {
            return null;
        }

        return DB::transaction(function () use ($rule): ?int {
            /** @var LeadRoutingRule|null $locked */
            $locked = LeadRoutingRule::query()
                ->whereKey($rule->id)
                ->lockForUpdate()
                ->first();

            if ($locked === null || ! $locked->isActive()) {
                return null;
            }

            $locked->load(['members' => fn ($query) => $query->where('users.is_active', true)->orderBy('users.name')]);

            if ($locked->members->isEmpty()) {
                return null;
            }

            $userId = match ($locked->distribution->value) {
                'equal' => $this->pickLeastLoaded($locked),
                'round_robin' => $this->pickRoundRobin($locked),
                'custom' => $this->pickWeighted($locked),
            };

            return $userId;
        });
    }

    private function matchRule(string $source, ?string $subSource): ?LeadRoutingRule
    {
        $rules = LeadRoutingRule::query()
            ->active()
            ->where('source', $source)
            ->with(['members'])
            ->get();

        if ($rules->isEmpty()) {
            return null;
        }

        if ($subSource !== null) {
            $exact = $rules->first(
                function (LeadRoutingRule $rule) use ($subSource): bool {
                    if (! filled($rule->sub_source)) {
                        return false;
                    }

                    return $this->subSourceMatches((string) $rule->sub_source, $subSource);
                },
            );

            if ($exact instanceof LeadRoutingRule) {
                return $exact;
            }
        }

        return $rules->first(fn (LeadRoutingRule $rule): bool => blank($rule->sub_source));
    }

    private function subSourceMatches(string $ruleSubSource, string $leadSubSource): bool
    {
        if (strcasecmp($ruleSubSource, $leadSubSource) === 0) {
            return true;
        }

        return str_contains(mb_strtolower($leadSubSource), mb_strtolower($ruleSubSource));
    }

    private function pickLeastLoaded(LeadRoutingRule $rule): ?int
    {
        $openStatuses = collect(LeadStatus::cases())
            ->reject(fn (LeadStatus $status): bool => $status->isClosed())
            ->map(fn (LeadStatus $status): string => $status->value)
            ->all();

        $memberIds = $rule->members->pluck('id')->all();

        $counts = Lead::query()
            ->whereIn('assigned_to_id', $memberIds)
            ->whereIn('status', $openStatuses)
            ->selectRaw('assigned_to_id, count(*) as aggregate')
            ->groupBy('assigned_to_id')
            ->pluck('aggregate', 'assigned_to_id');

        $bestId = null;
        $bestCount = null;

        foreach ($memberIds as $memberId) {
            $count = (int) ($counts[$memberId] ?? 0);

            if ($bestCount === null || $count < $bestCount) {
                $bestCount = $count;
                $bestId = (int) $memberId;
            }
        }

        return $bestId;
    }

    private function pickRoundRobin(LeadRoutingRule $rule): ?int
    {
        $members = $rule->members->values();

        if ($members->isEmpty()) {
            return null;
        }

        $index = $rule->distribution_cursor % $members->count();
        $user = $members->get($index);

        $rule->update([
            'distribution_cursor' => $rule->distribution_cursor + 1,
        ]);

        return $user instanceof User ? (int) $user->id : null;
    }

    private function pickWeighted(LeadRoutingRule $rule): ?int
    {
        $slots = [];

        foreach ($rule->members as $member) {
            $weight = max(1, (int) ($member->pivot->weight ?? 1));

            for ($i = 0; $i < $weight; $i++) {
                $slots[] = (int) $member->id;
            }
        }

        if ($slots === []) {
            return null;
        }

        $index = $rule->distribution_cursor % count($slots);
        $userId = $slots[$index];

        $rule->update([
            'distribution_cursor' => $rule->distribution_cursor + 1,
        ]);

        return $userId;
    }
}
