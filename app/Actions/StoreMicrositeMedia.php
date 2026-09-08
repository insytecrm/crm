<?php

namespace App\Actions;

use App\Models\Property;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreMicrositeMedia
{
    /**
     * @return array{id: string, name: string, path: string, disk: string, mime_type: string|null, size: int}|null
     */
    public function storeFile(Property $property, UploadedFile $file, string $folder): array
    {
        $path = $file->store("properties/{$property->id}/microsite/{$folder}", 'local');

        return [
            'id' => (string) Str::uuid(),
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'local',
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return list<array{id: string, name: string, path: string, disk: string, mime_type: string|null, size: int}>
     */
    public function storeFiles(Property $property, array $files, string $folder): array
    {
        $stored = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $stored[] = $this->storeFile($property, $file, $folder);
            }
        }

        return $stored;
    }

    /**
     * @param  array<string, mixed>|null  $file
     */
    public function deleteFile(?array $file): void
    {
        if (! is_array($file) || blank($file['path'] ?? null)) {
            return;
        }

        Storage::disk((string) ($file['disk'] ?? 'local'))->delete((string) $file['path']);
    }
}
