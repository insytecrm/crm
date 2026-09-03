<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreLeadDocumentRequest;
use App\Models\Lead;
use App\Models\LeadDocument;
use App\Support\LeadDrawerRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadDocumentController extends Controller
{
    public function store(StoreLeadDocumentRequest $request, Lead $lead): RedirectResponse
    {
        $file = $request->file('document');
        $path = $file->store("leads/{$lead->id}/documents", 'local');

        $lead->documents()->create([
            'uploaded_by_id' => auth()->id(),
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'local',
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return LeadDrawerRedirect::to($lead, __('Document uploaded.'));
    }

    public function download(Lead $lead, LeadDocument $document): StreamedResponse
    {
        abort_unless($document->lead_id === $lead->id, 404);

        return Storage::disk($document->disk)->download($document->path, $document->name);
    }

    public function destroy(Lead $lead, LeadDocument $document): RedirectResponse
    {
        abort_unless($document->lead_id === $lead->id, 404);

        $document->deleteFile();
        $document->delete();

        return LeadDrawerRedirect::to($lead, __('Document deleted.'));
    }
}
