<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\UpdatePropertyMicrositeContent;
use App\Enums\MicrositeFont;
use App\Enums\MicrositeTheme;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdatePropertyMicrositeContentRequest;
use App\Models\Property;
use App\Support\Microsite\PublicMicrositeView;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PropertyMicrositeCmsController extends Controller
{
    public function edit(Property $property, PublicMicrositeView $publicMicrositeView): View
    {
        abort_unless($property->hasMicrosite(), 404);

        $microsite = $property->microsite()->firstOrCreate([], [
            'theme_preset' => MicrositeTheme::Noir,
            'font_primary' => MicrositeFont::Outfit,
            'font_secondary' => MicrositeFont::CormorantGaramond,
            'content' => [],
            'media' => [],
            'lead_form' => [],
        ]);

        $property->setRelation('microsite', $microsite);

        return view('tenant.properties.microsite-cms', [
            'property' => $property,
            'microsite' => $microsite,
            'content' => $microsite->sectionContent(),
            'media' => $microsite->mediaLibrary(),
            'leadForm' => $microsite->leadFormSettings(),
            'themes' => MicrositeTheme::cases(),
            'primaryFonts' => MicrositeFont::primaryOptions(),
            'secondaryFonts' => MicrositeFont::secondaryOptions(),
            'defaults' => $publicMicrositeView->cmsDefaults($property),
        ]);
    }

    public function update(
        UpdatePropertyMicrositeContentRequest $request,
        Property $property,
        UpdatePropertyMicrositeContent $updatePropertyMicrositeContent,
    ): RedirectResponse {
        abort_unless($property->hasMicrosite(), 404);

        $updatePropertyMicrositeContent->handle($property, $request->payload());

        return back()->with('status', __('Microsite content saved.'));
    }
}
