<?php

namespace App\Actions;

use App\Models\TeamConversation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class StoreTeamMessageAttachments
{
    /**
     * @param  array<int, UploadedFile>|null  $files
     * @return array<int, array{id: string, name: string, path: string, disk: string, mime_type: string|null, size: int}>
     */
    public function handle(TeamConversation $conversation, ?array $files): array
    {
        $storedFiles = [];

        foreach ($files ?? [] as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store("team-chat/{$conversation->id}", 'local');

            $storedFiles[] = [
                'id' => (string) Str::uuid(),
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'disk' => 'local',
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ];
        }

        return $storedFiles;
    }
}
