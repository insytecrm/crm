<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\SendLeadWhatsAppMessage;
use App\Enums\MessageTemplateChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\SendLeadWhatsAppRequest;
use App\Models\Lead;
use App\Models\MessageTemplate;
use App\Support\LeadDrawerRedirect;
use App\Support\RenderMessageTemplate;
use App\Support\ResolveMessageTemplateValues;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadWhatsAppController extends Controller
{
    public function show(
        Request $request,
        Lead $lead,
        ResolveMessageTemplateValues $resolveMessageTemplateValues,
        RenderMessageTemplate $renderMessageTemplate,
    ): JsonResponse {
        abort_unless($request->user() !== null, 403);

        $values = $resolveMessageTemplateValues->handle($lead, $request->user());

        $templates = MessageTemplate::query()
            ->active()
            ->where('channel', MessageTemplateChannel::WhatsApp)
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'body'])
            ->map(fn (MessageTemplate $template): array => [
                'id' => $template->id,
                'name' => $template->name,
                'preview' => $renderMessageTemplate->handle($template->body, $values),
            ])
            ->values()
            ->all();

        return response()->json([
            'lead_name' => $lead->name,
            'has_phone' => $lead->whatsAppUrl() !== null,
            'whatsapp_digits' => $lead->whatsAppDigits(),
            'templates' => $templates,
        ]);
    }

    public function store(
        SendLeadWhatsAppRequest $request,
        Lead $lead,
        SendLeadWhatsAppMessage $sendLeadWhatsAppMessage,
    ): RedirectResponse {
        $template = $request->filled('template_id')
            ? MessageTemplate::query()
                ->active()
                ->where('channel', MessageTemplateChannel::WhatsApp)
                ->findOrFail($request->integer('template_id'))
            : null;

        $message = (string) $request->validated('message');

        $sendLeadWhatsAppMessage->handle(
            $lead,
            $request->user(),
            $message,
            $template,
        );

        $digits = $lead->whatsAppDigits();

        if ($digits !== null && $digits !== '') {
            return redirect()
                ->route('tenant.whatsapp-web.index', [
                    'tenant' => tenant('id'),
                    'phone' => $digits,
                    'message' => $message,
                ])
                ->with('status', __('WhatsApp opened with your message.'));
        }

        return LeadDrawerRedirect::to($lead, __('WhatsApp opened with your message.'));
    }
}
