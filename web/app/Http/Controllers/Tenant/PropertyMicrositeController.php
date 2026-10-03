<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\CreateMicrositeEnquiry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreMicrositeEnquiryRequest;
use App\Models\Property;
use App\Support\Microsite\PublicMicrositeView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PropertyMicrositeController extends Controller
{
    public function show(string $slug, PublicMicrositeView $publicMicrositeView): View
    {
        $property = $this->liveProperty($slug);

        return view('tenant.properties.microsite', [
            'page' => $publicMicrositeView->for($property),
        ]);
    }

    public function media(string $slug, string $key, PublicMicrositeView $publicMicrositeView): StreamedResponse
    {
        $property = $this->liveProperty($slug);
        $file = $publicMicrositeView->fileForKey($property, $key);

        abort_if($file === null, 404);

        $disk = (string) ($file['disk'] ?? 'local');
        $path = (string) $file['path'];

        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response($path, (string) ($file['name'] ?? 'file'), [
            'Content-Type' => (string) ($file['mime_type'] ?? 'application/octet-stream'),
        ]);
    }

    public function enquire(StoreMicrositeEnquiryRequest $request, string $slug, CreateMicrositeEnquiry $createMicrositeEnquiry): RedirectResponse
    {
        $property = $this->liveProperty($slug);

        $createMicrositeEnquiry->handle($property, $request->safe()->only(['name', 'phone', 'email', 'configuration']));

        $success = $property->microsite?->leadFormSettings()['success_message'] ?? null;

        return back()->with('status', $success ?: __('Thank you. Our team will share project details shortly.'));
    }

    private function liveProperty(string $slug): Property
    {
        return Property::query()
            ->with('microsite')
            ->where('microsite_slug', $slug)
            ->where('microsite_enabled', true)
            ->firstOrFail();
    }
}
