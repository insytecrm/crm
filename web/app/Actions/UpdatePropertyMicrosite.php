<?php

namespace App\Actions;

use App\Enums\PlanLimitKey;
use App\Models\Property;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdatePropertyMicrosite
{
    public function handle(Property $property, bool $enabled): Property
    {
        if (! $enabled) {
            $property->update([
                'microsite_enabled' => false,
            ]);

            return $property->refresh();
        }

        if (! $property->microsite_enabled) {
            app(AssertPlanLimit::class)->handle(PlanLimitKey::Microsites);
        }

        if (! tenant()?->canPublishMicrosites()) {
            throw ValidationException::withMessages([
                'microsite_enabled' => __('Verify your website domain in Settings → Domains before publishing microsites.'),
            ]);
        }

        $slug = $property->microsite_slug;

        if (blank($slug)) {
            $slug = $this->uniqueSlugFor($property);
        }

        $property->update([
            'microsite_enabled' => true,
            'microsite_slug' => $slug,
        ]);

        return $property->refresh();
    }

    private function uniqueSlugFor(Property $property): string
    {
        $base = Str::slug($property->project_name);

        if ($base === '') {
            $base = 'project-'.$property->id;
        }

        $slug = $base;
        $suffix = 2;

        while (
            Property::query()
                ->where('microsite_slug', $slug)
                ->whereKeyNot($property->id)
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
