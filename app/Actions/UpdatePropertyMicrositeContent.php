<?php

namespace App\Actions;

use App\Enums\MicrositeFont;
use App\Enums\MicrositeSection;
use App\Enums\MicrositeTheme;
use App\Models\Property;
use App\Models\PropertyMicrosite;
use Illuminate\Http\UploadedFile;

class UpdatePropertyMicrositeContent
{
    public function __construct(private StoreMicrositeMedia $storeMicrositeMedia) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Property $property, array $data): PropertyMicrosite
    {
        $microsite = $property->microsite()->firstOrCreate([], [
            'theme_preset' => MicrositeTheme::Noir,
            'font_primary' => MicrositeFont::Outfit,
            'font_secondary' => MicrositeFont::CormorantGaramond,
            'content' => [],
            'media' => [],
            'lead_form' => [],
        ]);

        $media = $microsite->mediaLibrary();

        foreach (['logo', 'hero', 'about', 'developer', 'location'] as $slot) {
            if (! empty($data['remove_'.$slot])) {
                $this->storeMicrositeMedia->deleteFile($media[$slot] ?? null);
                $media[$slot] = null;
            }

            $upload = $data[$slot.'_image'] ?? null;

            if ($upload instanceof UploadedFile) {
                $this->storeMicrositeMedia->deleteFile($media[$slot] ?? null);
                $media[$slot] = $this->storeMicrositeMedia->storeFile($property, $upload, $slot);
            }
        }

        $gallery = $media['gallery'];
        $removeIds = collect($data['remove_gallery'] ?? [])
            ->filter(fn (mixed $id): bool => is_string($id) && $id !== '')
            ->values()
            ->all();

        if ($removeIds !== []) {
            $kept = [];

            foreach ($gallery as $file) {
                if (in_array($file['id'], $removeIds, true)) {
                    $this->storeMicrositeMedia->deleteFile($file);

                    continue;
                }

                $kept[] = $file;
            }

            $gallery = $kept;
        }

        $galleryUploads = $data['gallery'] ?? [];

        if (is_array($galleryUploads) && $galleryUploads !== []) {
            $gallery = array_merge(
                $gallery,
                $this->storeMicrositeMedia->storeFiles($property, $galleryUploads, 'gallery'),
            );
        }

        $media['gallery'] = array_values($gallery);

        $microsite->update([
            'theme_preset' => $data['theme_preset'] ?? $microsite->theme()->value,
            'theme_accent' => $data['theme_accent'] ?? null,
            'font_primary' => $data['font_primary'] ?? $microsite->primaryFont()->value,
            'font_secondary' => $data['font_secondary'] ?? $microsite->secondaryFont()->value,
            'phone' => $data['phone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'cta_label' => $data['cta_label'] ?? null,
            'lead_form' => $this->normalizeLeadForm($data['lead_form'] ?? []),
            'content' => $this->normalizeContent($data['sections'] ?? []),
            'media' => $media,
        ]);

        return $microsite->refresh();
    }

    /**
     * @return array<string, array{headline: ?string, subheadline: ?string, body: ?string, button_label: ?string, video_url: ?string, map_url: ?string}>
     */
    private function normalizeContent(mixed $sections): array
    {
        $input = is_array($sections) ? $sections : [];
        $normalized = [];

        foreach (MicrositeSection::cases() as $section) {
            $row = is_array($input[$section->value] ?? null) ? $input[$section->value] : [];

            $normalized[$section->value] = [
                'headline' => $this->nullableString($row['headline'] ?? null),
                'subheadline' => $this->nullableString($row['subheadline'] ?? null),
                'body' => $this->nullableString($row['body'] ?? null),
                'button_label' => $this->nullableString($row['button_label'] ?? null),
                'video_url' => $this->nullableString($row['video_url'] ?? null),
                'map_url' => $this->nullableString($row['map_url'] ?? null),
            ];
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeLeadForm(mixed $leadForm): array
    {
        $input = is_array($leadForm) ? $leadForm : [];

        $showName = $this->boolValue($input['show_name'] ?? true, true);
        $showPhone = $this->boolValue($input['show_phone'] ?? true, true);
        $showEmail = $this->boolValue($input['show_email'] ?? true, true);
        $showConfiguration = $this->boolValue($input['show_configuration'] ?? true, true);

        return [
            'title' => $this->nullableString($input['title'] ?? null),
            'subtitle' => $this->nullableString($input['subtitle'] ?? null),
            'submit_label' => $this->nullableString($input['submit_label'] ?? null),
            'success_message' => $this->nullableString($input['success_message'] ?? null),
            'show_name' => $showName,
            'show_phone' => $showPhone,
            'show_email' => $showEmail,
            'show_configuration' => $showConfiguration,
            'require_name' => $showName && $this->boolValue($input['require_name'] ?? true, true),
            'require_phone' => $showPhone && $this->boolValue($input['require_phone'] ?? true, true),
            'require_email' => $showEmail && $this->boolValue($input['require_email'] ?? false, false),
            'require_configuration' => $showConfiguration && $this->boolValue($input['require_configuration'] ?? false, false),
            'label_name' => $this->nullableString($input['label_name'] ?? null),
            'label_phone' => $this->nullableString($input['label_phone'] ?? null),
            'label_email' => $this->nullableString($input['label_email'] ?? null),
            'label_configuration' => $this->nullableString($input['label_configuration'] ?? null),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function boolValue(mixed $value, bool $default): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
