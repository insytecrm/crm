<?php

namespace App\Queries;

use App\Models\Lead;
use App\Support\LeadContactNormalizer;
use Illuminate\Support\Collection;

class DuplicateLeadGroups
{
    /**
     * @return Collection<int, array{
     *     key: string,
     *     leads: Collection<int, Lead>,
     *     match_reasons: list<string>,
     * }>
     */
    public function all(): Collection
    {
        $leads = Lead::query()
            ->with('assignedTo')
            ->where(function ($query): void {
                $query->where(function ($query): void {
                    $query->whereNotNull('phone')->where('phone', '!=', '');
                })->orWhere(function ($query): void {
                    $query->whereNotNull('email')->where('email', '!=', '');
                });
            })
            ->orderBy('name')
            ->get();

        if ($leads->isEmpty()) {
            return collect();
        }

        /** @var array<int, int> $parent */
        $parent = [];

        foreach ($leads as $lead) {
            $parent[$lead->id] = $lead->id;
        }

        $find = function (int $leadId) use (&$parent, &$find): int {
            if ($parent[$leadId] !== $leadId) {
                $parent[$leadId] = $find($parent[$leadId]);
            }

            return $parent[$leadId];
        };

        $union = function (int $firstLeadId, int $secondLeadId) use (&$parent, $find): void {
            $firstRoot = $find($firstLeadId);
            $secondRoot = $find($secondLeadId);

            if ($firstRoot !== $secondRoot) {
                $parent[$secondRoot] = $firstRoot;
            }
        };

        /** @var array<string, int> $phoneIndex */
        $phoneIndex = [];

        /** @var array<string, int> $emailIndex */
        $emailIndex = [];

        foreach ($leads as $lead) {
            $normalizedPhone = LeadContactNormalizer::phone($lead->phone);

            if ($normalizedPhone !== null) {
                if (isset($phoneIndex[$normalizedPhone])) {
                    $union($phoneIndex[$normalizedPhone], $lead->id);
                } else {
                    $phoneIndex[$normalizedPhone] = $lead->id;
                }
            }

            $normalizedEmail = LeadContactNormalizer::email($lead->email);

            if ($normalizedEmail !== null) {
                if (isset($emailIndex[$normalizedEmail])) {
                    $union($emailIndex[$normalizedEmail], $lead->id);
                } else {
                    $emailIndex[$normalizedEmail] = $lead->id;
                }
            }
        }

        /** @var Collection<int, Collection<int, Lead>> $groupedLeads */
        $groupedLeads = collect();

        foreach ($leads as $lead) {
            $rootId = $find($lead->id);

            if (! $groupedLeads->has($rootId)) {
                $groupedLeads->put($rootId, collect());
            }

            $groupedLeads->get($rootId)?->push($lead);
        }

        return $groupedLeads
            ->filter(fn (Collection $group): bool => $group->count() > 1)
            ->values()
            ->map(function (Collection $group, int $index): array {
                $matchReasons = $this->matchReasons($group);

                return [
                    'key' => 'group-'.$index,
                    'leads' => $group->sortBy('name')->values(),
                    'match_reasons' => $matchReasons,
                ];
            })
            ->sortByDesc(fn (array $group): int => $group['leads']->count())
            ->values();
    }

    public function count(): int
    {
        return $this->all()->count();
    }

    /**
     * @param  Collection<int, Lead>  $leads
     * @return list<string>
     */
    private function matchReasons(Collection $leads): array
    {
        $reasons = [];

        $phones = $leads
            ->map(fn (Lead $lead): ?string => LeadContactNormalizer::phone($lead->phone))
            ->filter()
            ->countBy();

        foreach ($phones as $phone => $count) {
            if ($count > 1) {
                $reasons[] = __('Matching phone: :phone', ['phone' => $phone]);
            }
        }

        $emails = $leads
            ->map(fn (Lead $lead): ?string => LeadContactNormalizer::email($lead->email))
            ->filter()
            ->countBy();

        foreach ($emails as $email => $count) {
            if ($count > 1) {
                $reasons[] = __('Matching email: :email', ['email' => $email]);
            }
        }

        return array_values(array_unique($reasons));
    }
}
