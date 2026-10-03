<?php

namespace App\Actions;

use App\Models\TeamConversation;
use Illuminate\Support\Facades\DB;

class FindOrCreateDirectConversation
{
    public function handle(int $userId, int $recipientId): TeamConversation
    {
        $conversation = TeamConversation::query()
            ->whereHas('participants', fn ($query) => $query->where('users.id', $userId))
            ->whereHas('participants', fn ($query) => $query->where('users.id', $recipientId))
            ->has('participants', '=', 2)
            ->first();

        if ($conversation instanceof TeamConversation) {
            return $conversation;
        }

        return DB::transaction(function () use ($userId, $recipientId): TeamConversation {
            $conversation = TeamConversation::query()->create();

            $conversation->participants()->attach([$userId, $recipientId]);

            return $conversation->load('participants');
        });
    }
}
