<?php

namespace App\Models;

use App\Enums\MicrositeFont;
use App\Enums\MicrositeSection;
use App\Enums\MicrositeTheme;
use Database\Factories\PropertyMicrositeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'property_id',
    'theme_preset',
    'theme_accent',
    'font_primary',
    'font_secondary',
    'phone',
    'whatsapp',
    'cta_label',
    'lead_form',
    'content',
    'media',
])]
class PropertyMicrosite extends Model
{
    /** @use HasFactory<PropertyMicrositeFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'theme_preset' => MicrositeTheme::class,
            'font_primary' => MicrositeFont::class,
            'font_secondary' => MicrositeFont::class,
            'lead_form' => 'array',
            'content' => 'array',
            'media' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function theme(): MicrositeTheme
    {
        return $this->theme_preset instanceof MicrositeTheme
            ? $this->theme_preset
            : MicrositeTheme::Noir;
    }

    public function primaryFont(): MicrositeFont
    {
        return $this->font_primary instanceof MicrositeFont
            ? $this->font_primary
            : MicrositeFont::Outfit;
    }

    public function secondaryFont(): MicrositeFont
    {
        return $this->font_secondary instanceof MicrositeFont
            ? $this->font_secondary
            : MicrositeFont::CormorantGaramond;
    }

    /**
     * @return array{
     *     title: ?string,
     *     subtitle: ?string,
     *     submit_label: ?string,
     *     success_message: ?string,
     *     show_name: bool,
     *     show_phone: bool,
     *     show_email: bool,
     *     show_configuration: bool,
     *     require_name: bool,
     *     require_phone: bool,
     *     require_email: bool,
     *     require_configuration: bool,
     *     label_name: ?string,
     *     label_phone: ?string,
     *     label_email: ?string,
     *     label_configuration: ?string
     * }
     */
    public function leadFormSettings(): array
    {
        $stored = is_array($this->lead_form) ? $this->lead_form : [];

        $showName = $this->boolSetting($stored['show_name'] ?? true, true);
        $showPhone = $this->boolSetting($stored['show_phone'] ?? true, true);
        $showEmail = $this->boolSetting($stored['show_email'] ?? true, true);
        $showConfiguration = $this->boolSetting($stored['show_configuration'] ?? true, true);

        return [
            'title' => $this->nullableString($stored['title'] ?? null),
            'subtitle' => $this->nullableString($stored['subtitle'] ?? null),
            'submit_label' => $this->nullableString($stored['submit_label'] ?? null),
            'success_message' => $this->nullableString($stored['success_message'] ?? null),
            'show_name' => $showName,
            'show_phone' => $showPhone,
            'show_email' => $showEmail,
            'show_configuration' => $showConfiguration,
            'require_name' => $showName && $this->boolSetting($stored['require_name'] ?? true, true),
            'require_phone' => $showPhone && $this->boolSetting($stored['require_phone'] ?? true, true),
            'require_email' => $showEmail && $this->boolSetting($stored['require_email'] ?? false, false),
            'require_configuration' => $showConfiguration && $this->boolSetting($stored['require_configuration'] ?? false, false),
            'label_name' => $this->nullableString($stored['label_name'] ?? null),
            'label_phone' => $this->nullableString($stored['label_phone'] ?? null),
            'label_email' => $this->nullableString($stored['label_email'] ?? null),
            'label_configuration' => $this->nullableString($stored['label_configuration'] ?? null),
        ];
    }

    /**
     * @return array<string, array{headline: ?string, subheadline: ?string, body: ?string, button_label: ?string, video_url: ?string, map_url: ?string}>
     */
    public function sectionContent(): array
    {
        $stored = is_array($this->content) ? $this->content : [];
        $sections = [];

        foreach (MicrositeSection::cases() as $section) {
            $row = is_array($stored[$section->value] ?? null) ? $stored[$section->value] : [];

            $sections[$section->value] = [
                'headline' => $this->nullableString($row['headline'] ?? null),
                'subheadline' => $this->nullableString($row['subheadline'] ?? null),
                'body' => $this->nullableString($row['body'] ?? null),
                'button_label' => $this->nullableString($row['button_label'] ?? null),
                'video_url' => $this->nullableString($row['video_url'] ?? null),
                'map_url' => $this->nullableString($row['map_url'] ?? null),
            ];
        }

        return $sections;
    }

    /**
     * @return array{
     *     logo: ?array<string, mixed>,
     *     hero: ?array<string, mixed>,
     *     about: ?array<string, mixed>,
     *     developer: ?array<string, mixed>,
     *     location: ?array<string, mixed>,
     *     gallery: list<array<string, mixed>>
     * }
     */
    public function mediaLibrary(): array
    {
        $stored = is_array($this->media) ? $this->media : [];

        return [
            'logo' => $this->fileRecord($stored['logo'] ?? null),
            'hero' => $this->fileRecord($stored['hero'] ?? null),
            'about' => $this->fileRecord($stored['about'] ?? null),
            'developer' => $this->fileRecord($stored['developer'] ?? null),
            'location' => $this->fileRecord($stored['location'] ?? null),
            'gallery' => collect($stored['gallery'] ?? [])
                ->filter(fn (mixed $file): bool => is_array($file) && filled($file['path'] ?? null))
                ->map(fn (array $file): array => $this->fileRecord($file) ?? [])
                ->filter()
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{id: string, name: string, path: string, disk: string, mime_type: ?string, size: int}|null
     */
    public function fileByKey(string $key): ?array
    {
        $media = $this->mediaLibrary();

        return match (true) {
            $key === 'logo' => $media['logo'],
            $key === 'hero' => $media['hero'],
            $key === 'about' => $media['about'],
            $key === 'developer' => $media['developer'],
            $key === 'location' => $media['location'],
            preg_match('/^gallery-(\d+)$/', $key, $matches) === 1 => $media['gallery'][(int) $matches[1]] ?? null,
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>|null  $file
     * @return array{id: string, name: string, path: string, disk: string, mime_type: ?string, size: int}|null
     */
    private function fileRecord(mixed $file): ?array
    {
        if (! is_array($file) || blank($file['path'] ?? null)) {
            return null;
        }

        return [
            'id' => (string) ($file['id'] ?? Str::uuid()),
            'name' => (string) ($file['name'] ?? 'file'),
            'path' => (string) $file['path'],
            'disk' => (string) ($file['disk'] ?? 'local'),
            'mime_type' => isset($file['mime_type']) ? (string) $file['mime_type'] : null,
            'size' => (int) ($file['size'] ?? 0),
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

    private function boolSetting(mixed $value, bool $default): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
