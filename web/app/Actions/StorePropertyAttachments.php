<?php

namespace App\Actions;

use App\Models\Property;
use Illuminate\Http\UploadedFile;

class StorePropertyAttachments
{
    /**
     * @param  array<int, UploadedFile>|null  $layoutFiles
     * @param  array<int, UploadedFile>|null  $brochureFiles
     * @return array{layout_files: array<int, array{name: string, path: string, disk: string, mime_type: string|null, size: int}>, brochure_files: array<int, array{name: string, path: string, disk: string, mime_type: string|null, size: int}>}
     */
    public function handle(Property $property, ?array $layoutFiles, ?array $brochureFiles): array
    {
        return [
            'layout_files' => $this->storeFiles($property, $layoutFiles ?? [], 'layouts'),
            'brochure_files' => $this->storeFiles($property, $brochureFiles ?? [], 'brochures'),
        ];
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, array{name: string, path: string, disk: string, mime_type: string|null, size: int}>
     */
    private function storeFiles(Property $property, array $files, string $folder): array
    {
        $storedFiles = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store("properties/{$property->id}/{$folder}", 'local');

            $storedFiles[] = [
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
