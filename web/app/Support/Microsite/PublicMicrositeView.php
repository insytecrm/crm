<?php

namespace App\Support\Microsite;

use App\Enums\MicrositeFont;
use App\Enums\MicrositeSection;
use App\Enums\MicrositeTheme;
use App\Models\Property;
use App\Models\PropertyMicrosite;
use Illuminate\Support\Str;

class PublicMicrositeView
{
    public function __construct(
        private GroupedPropertyConfigurations $groupedPropertyConfigurations = new GroupedPropertyConfigurations,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function for(Property $property): array
    {
        $cms = $property->microsite;
        $content = $cms?->sectionContent() ?? $this->emptyContent();
        $media = $cms?->mediaLibrary() ?? $this->emptyMedia();
        $theme = $this->themeTokens($cms);
        $configurations = $this->groupedPropertyConfigurations->group($property->configurations);
        $amenities = collect($property->amenities ?? [])
            ->filter(fn (mixed $amenity): bool => is_string($amenity) && trim($amenity) !== '')
            ->map(fn (string $amenity): string => trim($amenity))
            ->values()
            ->all();

        $startingPrice = MicrositeMoney::rupees($property->price_from);
        $ctaLabel = $cms?->cta_label ?: __('Enquire Now');
        $leadForm = $this->resolvedLeadForm($cms, $ctaLabel);
        $fonts = $this->fontTokens($cms);
        $heroVideo = filled($content['hero']['video_url'] ?? null)
            ? MicrositeEmbed::video((string) $content['hero']['video_url'])
            : null;
        $heroImage = $this->mediaUrl($property, 'hero')
            ?? $this->firstPublicImageUrl($property, 'brochure')
            ?? $this->firstPublicImageUrl($property, 'layout');

        $phone = $cms?->phone;
        $whatsapp = $cms?->whatsapp;
        $whatsappLink = $this->whatsappLink($whatsapp);

        $sections = [
            'highlights' => $this->hasHighlights($property),
            'about' => true,
            'configurations' => $configurations !== [],
            'amenities' => $amenities !== [],
            'visuals' => $this->hasVisuals($property, $media, $content),
            'location' => filled($property->project_location) || filled($content['location']['map_url'] ?? null),
            'developer' => filled($property->developer_name) || filled($content['developer']['body'] ?? null),
            'trust' => filled($property->rera_number) || $property->project_status !== null || filled($property->possession_date),
        ];

        return [
            'property_id' => $property->id,
            'slug' => $property->microsite_slug,
            'theme' => $theme,
            'fonts' => $fonts,
            'cta_label' => $ctaLabel,
            'buttons' => [
                'header' => $ctaLabel,
                'hero' => $content['hero']['button_label'] ?: __('Get Price & Availability'),
                'configurations' => $content['configurations']['button_label'] ?: $ctaLabel,
                'visuals' => $content['visuals']['button_label'] ?: __('Download Brochure'),
                'cta' => $content['cta']['button_label'] ?: $ctaLabel,
                'mobile' => $ctaLabel,
            ],
            'lead_form' => $leadForm,
            'phone' => $phone,
            'phone_link' => filled($phone) ? 'tel:'.preg_replace('/\s+/', '', (string) $phone) : null,
            'whatsapp' => $whatsapp,
            'whatsapp_link' => $whatsappLink,
            'enquire_url' => route('tenant.projects.microsite.enquire', [
                'tenant' => tenant('id'),
                'slug' => $property->microsite_slug,
            ]),
            'nav' => $this->nav($sections),
            'hero' => [
                'kicker' => collect([
                    $property->project_status?->label(),
                    $property->property_type?->label(),
                ])->filter()->implode(' · '),
                'headline' => $content['hero']['headline'] ?? $property->project_name,
                'subheadline' => $content['hero']['subheadline'] ?? $this->heroSubheadline($property),
                'developer' => $property->developer_name,
                'location' => $property->project_location,
                'starting_price' => $startingPrice,
                'status' => $property->project_status?->label(),
                'property_type' => $property->property_type?->label(),
                'image_url' => $heroVideo ? null : $heroImage,
                'video_embed' => $heroVideo,
                'cta_label' => $content['hero']['button_label'] ?: __('Get Price & Availability'),
            ],
            'highlights' => [
                'visible' => $sections['highlights'],
                'headline' => $content['highlights']['headline'] ?? __('Project highlights'),
                'subheadline' => $content['highlights']['subheadline'],
                'items' => $this->highlightItems($property),
            ],
            'about' => [
                'visible' => $sections['about'],
                'headline' => $content['about']['headline'] ?? __('About :project', ['project' => $property->project_name]),
                'subheadline' => $content['about']['subheadline'],
                'body' => $content['about']['body'] ?? $this->aboutFallback($property),
                'image_url' => $this->mediaUrl($property, 'about'),
                'facts' => array_values(array_filter([
                    filled($property->developer_name) ? ['label' => __('Developer'), 'value' => $property->developer_name] : null,
                    filled($property->project_location) ? ['label' => __('Location'), 'value' => $property->project_location] : null,
                    $property->project_status ? ['label' => __('Status'), 'value' => $property->project_status->label()] : null,
                    filled($property->possession_date) ? ['label' => __('Possession'), 'value' => $property->possession_date] : null,
                    filled($property->rera_number) ? ['label' => __('RERA'), 'value' => $property->rera_number] : null,
                ])),
            ],
            'configurations' => [
                'visible' => $sections['configurations'],
                'headline' => $content['configurations']['headline'] ?? __('Find the right space'),
                'subheadline' => $content['configurations']['subheadline'] ?? __('Explore configurations grouped by type.'),
                'button_label' => $content['configurations']['button_label'] ?: $ctaLabel,
                'groups' => $configurations,
            ],
            'amenities' => [
                'visible' => $sections['amenities'],
                'headline' => $content['amenities']['headline'] ?? __('Amenities'),
                'subheadline' => $content['amenities']['subheadline'],
                'items' => $amenities,
            ],
            'visuals' => [
                'visible' => $sections['visuals'],
                'headline' => $content['visuals']['headline'] ?? __('Brochures & layouts'),
                'subheadline' => $content['visuals']['subheadline'],
                'button_label' => $content['visuals']['button_label'] ?: __('Download Brochure'),
                'video_embed' => filled($content['visuals']['video_url'] ?? null)
                    ? MicrositeEmbed::video((string) $content['visuals']['video_url'])
                    : null,
                'gallery' => $this->gallery($property, $media),
                'brochures' => $this->attachmentLinks($property, 'brochure'),
                'layouts' => $this->attachmentLinks($property, 'layout'),
            ],
            'location' => [
                'visible' => $sections['location'],
                'headline' => $content['location']['headline'] ?? (filled($property->project_location) ? $property->project_location : __('Location')),
                'subheadline' => $content['location']['subheadline'],
                'address' => $property->project_location,
                'map_embed' => MicrositeEmbed::map($content['location']['map_url'] ?? null, $property->project_location),
                'image_url' => $this->mediaUrl($property, 'location'),
            ],
            'developer' => [
                'visible' => $sections['developer'],
                'headline' => $content['developer']['headline'] ?? $property->developer_name,
                'subheadline' => $content['developer']['subheadline'],
                'body' => $content['developer']['body'],
                'name' => $property->developer_name,
                'image_url' => $this->mediaUrl($property, 'developer'),
            ],
            'trust' => [
                'visible' => $sections['trust'],
                'items' => array_values(array_filter([
                    filled($property->rera_number) ? ['label' => __('RERA Number'), 'value' => $property->rera_number] : null,
                    $property->project_status ? ['label' => __('Project Status'), 'value' => $property->project_status->label()] : null,
                    filled($property->possession_date) ? ['label' => __('Possession Date'), 'value' => $property->possession_date] : null,
                ])),
            ],
            'cta' => [
                'headline' => $content['cta']['headline'] ?? __('Get project details'),
                'subheadline' => $content['cta']['subheadline'] ?? __('Enquire for pricing, availability, and floor plans.'),
                'button_label' => $content['cta']['button_label'] ?: $ctaLabel,
            ],
            'logo_url' => $this->mediaUrl($property, 'logo'),
            'project_name' => $property->project_name,
        ];
    }

    /**
     * @return array{id: string, name: string, path: string, disk: string, mime_type: ?string, size: int}|null
     */
    public function fileForKey(Property $property, string $key): ?array
    {
        if (in_array($key, ['logo', 'hero', 'about', 'developer', 'location'], true) || str_starts_with($key, 'gallery-')) {
            return $property->microsite?->fileByKey($key);
        }

        if (preg_match('/^(brochure|layout)-(\d+)$/', $key, $matches) === 1) {
            $files = $matches[1] === 'brochure'
                ? ($property->brochure_files ?? [])
                : ($property->layout_files ?? []);
            $file = $files[(int) $matches[2]] ?? null;

            return is_array($file) && filled($file['path'] ?? null) ? $file : null;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function cmsDefaults(Property $property): array
    {
        $page = $this->for($property);

        return [
            'hero' => [
                'headline' => $property->project_name,
                'subheadline' => $this->heroSubheadline($property),
                'button_label' => __('Get Price & Availability'),
            ],
            'highlights' => [
                'headline' => __('Project highlights'),
            ],
            'about' => [
                'headline' => __('About :project', ['project' => $property->project_name]),
                'body' => $this->aboutFallback($property),
            ],
            'configurations' => [
                'headline' => __('Find the right space'),
                'subheadline' => __('Explore configurations grouped by type.'),
                'button_label' => $page['cta_label'],
            ],
            'amenities' => [
                'headline' => __('Amenities'),
            ],
            'visuals' => [
                'headline' => __('Brochures & layouts'),
                'button_label' => __('Download Brochure'),
            ],
            'location' => [
                'headline' => $property->project_location ?: __('Location'),
            ],
            'developer' => [
                'headline' => $property->developer_name,
            ],
            'cta' => [
                'headline' => __('Get project details'),
                'subheadline' => __('Enquire for pricing, availability, and floor plans.'),
                'button_label' => $page['cta_label'],
            ],
            'cta_label' => $page['cta_label'],
            'lead_form' => [
                'title' => $page['lead_form']['title'],
                'subtitle' => $page['lead_form']['subtitle'],
                'submit_label' => $page['lead_form']['submit_label'],
                'success_message' => $page['lead_form']['success_message'],
                'label_name' => __('Name'),
                'label_phone' => __('Phone'),
                'label_email' => __('Email'),
                'label_configuration' => __('Configuration'),
            ],
        ];
    }

    /**
     * @return array{primary: string, secondary: string, bunny_href: string, primary_family: string, secondary_family: string}
     */
    private function fontTokens(?PropertyMicrosite $cms): array
    {
        $primary = $cms?->primaryFont() ?? MicrositeFont::Outfit;
        $secondary = $cms?->secondaryFont() ?? MicrositeFont::CormorantGaramond;
        $families = collect([$primary->bunnySlug(), $secondary->bunnySlug()])->unique()->implode('&family=');

        return [
            'primary' => $primary->value,
            'secondary' => $secondary->value,
            'primary_family' => $primary->cssFamily(),
            'secondary_family' => $secondary->cssFamily(),
            'bunny_href' => 'https://fonts.bunny.net/css?family='.$families.'&display=swap',
        ];
    }

    /**
     * @return array{
     *     title: string,
     *     subtitle: string,
     *     submit_label: string,
     *     success_message: string,
     *     show_name: bool,
     *     show_phone: bool,
     *     show_email: bool,
     *     show_configuration: bool,
     *     require_name: bool,
     *     require_phone: bool,
     *     require_email: bool,
     *     require_configuration: bool,
     *     label_name: string,
     *     label_phone: string,
     *     label_email: string,
     *     label_configuration: string
     * }
     */
    private function resolvedLeadForm(?PropertyMicrosite $cms, string $ctaLabel): array
    {
        $settings = $cms?->leadFormSettings() ?? [
            'title' => null,
            'subtitle' => null,
            'submit_label' => null,
            'success_message' => null,
            'show_name' => true,
            'show_phone' => true,
            'show_email' => true,
            'show_configuration' => true,
            'require_name' => true,
            'require_phone' => true,
            'require_email' => false,
            'require_configuration' => false,
            'label_name' => null,
            'label_phone' => null,
            'label_email' => null,
            'label_configuration' => null,
        ];

        return [
            'title' => $settings['title'] ?: $ctaLabel,
            'subtitle' => $settings['subtitle'] ?: __('Share your details for pricing and availability.'),
            'submit_label' => $settings['submit_label'] ?: $ctaLabel,
            'success_message' => $settings['success_message'] ?: __('Thank you. Our team will share project details shortly.'),
            'show_name' => (bool) $settings['show_name'],
            'show_phone' => (bool) $settings['show_phone'],
            'show_email' => (bool) $settings['show_email'],
            'show_configuration' => (bool) $settings['show_configuration'],
            'require_name' => (bool) $settings['require_name'],
            'require_phone' => (bool) $settings['require_phone'],
            'require_email' => (bool) $settings['require_email'],
            'require_configuration' => (bool) $settings['require_configuration'],
            'label_name' => $settings['label_name'] ?: __('Name'),
            'label_phone' => $settings['label_phone'] ?: __('Phone'),
            'label_email' => $settings['label_email'] ?: __('Email'),
            'label_configuration' => $settings['label_configuration'] ?: __('Configuration'),
        ];
    }

    private function heroSubheadline(Property $property): ?string
    {
        $parts = array_values(array_filter([
            $property->developer_name,
            $property->project_location,
        ]));

        return $parts === [] ? null : implode(' · ', $parts);
    }

    private function aboutFallback(Property $property): string
    {
        $bits = array_values(array_filter([
            $property->project_name,
            $property->developer_name ? __('by :developer', ['developer' => $property->developer_name]) : null,
            $property->project_location ? __('in :location', ['location' => $property->project_location]) : null,
            $property->project_status?->label(),
            $property->possession_date ? __('Possession :date', ['date' => $property->possession_date]) : null,
            $property->rera_number ? __('RERA :rera', ['rera' => $property->rera_number]) : null,
        ]));

        return implode('. ', $bits).'.';
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function highlightItems(Property $property): array
    {
        $carpet = match (true) {
            $property->carpet_area_from_sqft && $property->carpet_area_to_sqft => number_format((int) $property->carpet_area_from_sqft).' – '.number_format((int) $property->carpet_area_to_sqft).' '.__('sq.ft'),
            filled($property->carpet_area_from_sqft) => __('From').' '.number_format((int) $property->carpet_area_from_sqft).' '.__('sq.ft'),
            default => null,
        };

        return array_values(array_filter([
            filled($property->total_land_parcel_acres) ? ['label' => __('Land parcel'), 'value' => rtrim(rtrim((string) $property->total_land_parcel_acres, '0'), '.').' '.__('acres')] : null,
            filled($property->total_towers) ? ['label' => __('Towers'), 'value' => (string) $property->total_towers] : null,
            filled($property->total_floors) ? ['label' => __('Floors'), 'value' => (string) $property->total_floors] : null,
            $carpet ? ['label' => __('Carpet from'), 'value' => $carpet] : null,
            filled($property->possession_date) ? ['label' => __('Possession'), 'value' => $property->possession_date] : null,
        ]));
    }

    /**
     * @param  array<string, bool>  $sections
     * @return list<array{id: string, label: string}>
     */
    private function nav(array $sections): array
    {
        $items = [];

        if ($sections['about'] || $sections['highlights']) {
            $items[] = ['id' => 'overview', 'label' => __('Overview')];
        }

        if ($sections['configurations']) {
            $items[] = ['id' => 'configurations', 'label' => __('Configurations')];
        }

        if ($sections['amenities']) {
            $items[] = ['id' => 'amenities', 'label' => __('Amenities')];
        }

        if ($sections['location']) {
            $items[] = ['id' => 'location', 'label' => __('Location')];
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $media
     * @param  array<string, mixed>  $content
     */
    private function hasVisuals(Property $property, array $media, array $content): bool
    {
        return ($media['gallery'] ?? []) !== []
            || ($property->brochure_files ?? []) !== []
            || ($property->layout_files ?? []) !== []
            || filled($content['visuals']['video_url'] ?? null);
    }

    private function hasHighlights(Property $property): bool
    {
        return filled($property->total_land_parcel_acres)
            || filled($property->total_towers)
            || filled($property->total_floors)
            || filled($property->carpet_area_from_sqft)
            || filled($property->possession_date);
    }

    /**
     * @param  array<string, mixed>  $media
     * @return list<array{url: string, name: string}>
     */
    private function gallery(Property $property, array $media): array
    {
        $items = [];

        foreach ($media['gallery'] as $index => $file) {
            if (! $this->isImage($file)) {
                continue;
            }

            $items[] = [
                'url' => $this->publicMediaUrl($property, 'gallery-'.$index),
                'name' => (string) ($file['name'] ?? __('Image')),
            ];
        }

        foreach (['brochure', 'layout'] as $kind) {
            $files = $kind === 'brochure' ? ($property->brochure_files ?? []) : ($property->layout_files ?? []);

            foreach ($files as $index => $file) {
                if (! is_array($file) || ! $this->isImage($file)) {
                    continue;
                }

                $items[] = [
                    'url' => $this->publicMediaUrl($property, $kind.'-'.$index),
                    'name' => (string) ($file['name'] ?? __('Image')),
                ];
            }
        }

        return $items;
    }

    /**
     * @return list<array{url: string, name: string}>
     */
    private function attachmentLinks(Property $property, string $kind): array
    {
        $files = $kind === 'brochure' ? ($property->brochure_files ?? []) : ($property->layout_files ?? []);
        $links = [];

        foreach ($files as $index => $file) {
            if (! is_array($file) || blank($file['path'] ?? null)) {
                continue;
            }

            $links[] = [
                'url' => $this->publicMediaUrl($property, $kind.'-'.$index),
                'name' => (string) ($file['name'] ?? __('File')),
            ];
        }

        return $links;
    }

    private function mediaUrl(Property $property, string $key): ?string
    {
        $file = $property->microsite?->fileByKey($key);

        if ($file === null || ! $this->isImage($file)) {
            return null;
        }

        return $this->publicMediaUrl($property, $key);
    }

    private function firstPublicImageUrl(Property $property, string $kind): ?string
    {
        $files = $kind === 'brochure' ? ($property->brochure_files ?? []) : ($property->layout_files ?? []);

        foreach ($files as $index => $file) {
            if (is_array($file) && $this->isImage($file)) {
                return $this->publicMediaUrl($property, $kind.'-'.$index);
            }
        }

        return null;
    }

    private function publicMediaUrl(Property $property, string $key): string
    {
        return route('tenant.projects.microsite.media', [
            'tenant' => tenant('id'),
            'slug' => $property->microsite_slug,
            'key' => $key,
        ]);
    }

    /**
     * @param  array<string, mixed>  $file
     */
    private function isImage(array $file): bool
    {
        $mime = Str::lower((string) ($file['mime_type'] ?? ''));

        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        $name = Str::lower((string) ($file['name'] ?? $file['path'] ?? ''));

        return str_ends_with($name, '.jpg')
            || str_ends_with($name, '.jpeg')
            || str_ends_with($name, '.png')
            || str_ends_with($name, '.webp');
    }

    /**
     * @return array{preset: string, bg: string, surface: string, text: string, muted: string, border: string, accent: string, accent_text: string, hero: string}
     */
    private function themeTokens(?PropertyMicrosite $cms): array
    {
        $theme = $cms?->theme() ?? MicrositeTheme::Noir;
        $tokens = $theme->tokens();

        if (filled($cms?->theme_accent) && preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $cms->theme_accent) === 1) {
            $tokens['accent'] = (string) $cms->theme_accent;
        }

        $tokens['preset'] = $theme->value;

        return $tokens;
    }

    private function whatsappLink(?string $whatsapp): ?string
    {
        if (blank($whatsapp)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $whatsapp);

        return filled($digits) ? 'https://wa.me/'.$digits : null;
    }

    /**
     * @return array<string, array{headline: null, subheadline: null, body: null, video_url: null, map_url: null}>
     */
    private function emptyContent(): array
    {
        $sections = [];

        foreach (MicrositeSection::cases() as $section) {
            $sections[$section->value] = [
                'headline' => null,
                'subheadline' => null,
                'body' => null,
                'button_label' => null,
                'video_url' => null,
                'map_url' => null,
            ];
        }

        return $sections;
    }

    /**
     * @return array{logo: null, hero: null, about: null, developer: null, location: null, gallery: array<int, mixed>}
     */
    private function emptyMedia(): array
    {
        return [
            'logo' => null,
            'hero' => null,
            'about' => null,
            'developer' => null,
            'location' => null,
            'gallery' => [],
        ];
    }
}
