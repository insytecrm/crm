<?php

namespace App\Actions;

use App\Enums\FacebookPageConnectionStatus;
use App\Models\FacebookPageConnection;
use App\Models\User;
use InvalidArgumentException;

class CreateFacebookPageConnection
{
    /**
     * @param  array{page_id: string}  $data
     */
    public function handle(array $data, ?User $user = null): FacebookPageConnection
    {
        $user ??= auth()->user();
        $pageId = preg_replace('/\D+/', '', $data['page_id']) ?? '';

        if ($pageId === '') {
            throw new InvalidArgumentException(__('Enter a valid Facebook Page ID.'));
        }

        $existing = FacebookPageConnection::query()->first();

        if ($existing !== null) {
            throw new InvalidArgumentException(__('This workspace already has a Facebook Page connected. Disconnect it before adding another.'));
        }

        if (FacebookPageConnection::query()->where('page_id', $pageId)->exists()) {
            throw new InvalidArgumentException(__('This Facebook Page is already connected in this workspace.'));
        }

        return FacebookPageConnection::query()->create([
            'created_by_id' => $user?->id,
            'page_id' => $pageId,
            'status' => FacebookPageConnectionStatus::Draft,
        ]);
    }
}
