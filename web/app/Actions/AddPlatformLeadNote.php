<?php

namespace App\Actions;

use App\Enums\PlatformLeadActivityType;
use App\Models\PlatformLead;
use App\Models\PlatformLeadNote;
use App\Models\User;

class AddPlatformLeadNote
{
    public function __construct(
        private LogPlatformLeadActivity $logActivity,
    ) {}

    public function handle(PlatformLead $lead, string $body, ?User $user = null): PlatformLeadNote
    {
        $note = $lead->notes()->create([
            'user_id' => $user?->id ?? auth()->id(),
            'body' => trim($body),
        ]);

        $this->logActivity->handle(
            $lead,
            PlatformLeadActivityType::NoteAdded,
            __('Note added'),
            $user,
            ['note_id' => $note->id],
        );

        return $note;
    }
}
