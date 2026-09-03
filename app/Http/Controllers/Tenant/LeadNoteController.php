<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\LogLeadActivity;
use App\Enums\LeadActivityType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreLeadNoteRequest;
use App\Http\Requests\Tenant\UpdateLeadNoteRequest;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Support\LeadDrawerRedirect;
use Illuminate\Http\RedirectResponse;

class LeadNoteController extends Controller
{
    public function store(StoreLeadNoteRequest $request, Lead $lead, LogLeadActivity $logLeadActivity): RedirectResponse
    {
        $note = $lead->notes()->create([
            'body' => $request->validated('body'),
            'user_id' => auth()->id(),
        ]);

        $logLeadActivity->handle(
            $lead,
            LeadActivityType::NoteAdded,
            __('Note added'),
            metadata: [
                'note_id' => $note->id,
                'body' => $note->body,
            ],
        );

        return LeadDrawerRedirect::to($lead, __('Note added.'));
    }

    public function update(UpdateLeadNoteRequest $request, Lead $lead, LeadNote $note): RedirectResponse
    {
        abort_unless($note->lead_id === $lead->id, 404);
        abort_unless($note->user_id === auth()->id(), 403);

        $note->update($request->validated());

        return LeadDrawerRedirect::to($lead, __('Note updated.'));
    }

    public function destroy(Lead $lead, LeadNote $note): RedirectResponse
    {
        abort_unless($note->lead_id === $lead->id, 404);
        abort_unless($note->user_id === auth()->id(), 403);

        $note->delete();

        return LeadDrawerRedirect::to($lead, __('Note deleted.'));
    }
}
