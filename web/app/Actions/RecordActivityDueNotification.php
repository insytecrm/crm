<?php

namespace App\Actions;

use App\Models\LeadScheduledEvent;
use App\Models\LeadTask;
use App\Models\User;
use App\Notifications\ActivityDueNotification;
use Illuminate\Support\Collection;

class RecordActivityDueNotification
{
    /**
     * @param  list<array<string, mixed>>  $dueItems
     */
    public function syncForUser(User $user, array $dueItems): void
    {
        if ($dueItems === []) {
            return;
        }

        $existing = $this->existingKeys($user);

        foreach ($dueItems as $item) {
            $key = $item['subject_type'].':'.$item['subject_id'];

            if ($existing->contains($key)) {
                continue;
            }

            $notification = $this->makeNotification($item);

            if ($notification === null) {
                continue;
            }

            $user->notify($notification);
            $existing->push($key);
        }
    }

    /**
     * @return Collection<int, string>
     */
    private function existingKeys(User $user): Collection
    {
        return $user->notifications()
            ->where('type', ActivityDueNotification::class)
            ->get(['data'])
            ->map(function ($notification): ?string {
                $data = $notification->data;

                if (! is_array($data)) {
                    return null;
                }

                $type = $data['subject_type'] ?? null;
                $id = $data['subject_id'] ?? null;

                if (! is_string($type) || $id === null) {
                    return null;
                }

                return $type.':'.$id;
            })
            ->filter()
            ->values();
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function makeNotification(array $item): ?ActivityDueNotification
    {
        if (($item['subject_type'] ?? null) === 'task') {
            $task = LeadTask::query()->with('lead')->find($item['subject_id'] ?? null);

            return $task ? ActivityDueNotification::fromTask($task) : null;
        }

        $event = LeadScheduledEvent::query()->with('lead')->find($item['subject_id'] ?? null);

        return $event ? ActivityDueNotification::fromScheduledEvent($event) : null;
    }
}
