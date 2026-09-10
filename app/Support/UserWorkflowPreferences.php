<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;

class UserWorkflowPreferences
{
    public const string StorageKey = 'workflow';

    public function __construct(private User $user) {}

    public static function for(?User $user = null): self
    {
        $user ??= auth()->user();

        if (! $user instanceof User) {
            throw new \RuntimeException('Authenticated user required for workflow preferences.');
        }

        return new self($user);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $workflow = $this->user->preferences[self::StorageKey] ?? [];

        if (! is_array($workflow)) {
            return $default;
        }

        return $workflow[$key] ?? $default;
    }

    public function remember(string $key, mixed $value): void
    {
        $preferences = $this->user->preferences ?? [];
        $workflow = $preferences[self::StorageKey] ?? [];

        if (! is_array($workflow)) {
            $workflow = [];
        }

        $workflow[$key] = $value;
        $preferences[self::StorageKey] = $workflow;

        $this->user->forceFill(['preferences' => $preferences])->save();
    }

    public function defaultFollowUpAt(): string
    {
        return Carbon::now()
            ->timezone(config('app.timezone'))
            ->addDay()
            ->setTime(10, 0, 0)
            ->format('Y-m-d\TH:i');
    }

    public function followUpAtValue(?string $submitted = null): string
    {
        if (filled($submitted)) {
            return $submitted;
        }

        return $this->defaultFollowUpAt();
    }
}
