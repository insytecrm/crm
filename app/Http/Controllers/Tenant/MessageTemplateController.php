<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\MessageTemplateChannel;
use App\Enums\MessageTemplateFilter;
use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreMessageTemplateRequest;
use App\Http\Requests\Tenant\UpdateMessageTemplateRequest;
use App\Models\MessageTemplate;
use App\Support\TemplateVariableCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageTemplateController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::AutomationsView), 403);

        $filter = MessageTemplateFilter::fromRequest($request->string('filter')->toString());
        $search = $request->string('search')->trim()->toString();

        $templates = MessageTemplate::query()
            ->with('createdBy:id,name')
            ->when($filter->channel(), fn ($query, MessageTemplateChannel $channel) => $query->where('channel', $channel))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('body', 'like', '%'.$search.'%')
                        ->orWhere('subject', 'like', '%'.$search.'%');
                });
            })
            ->latest('updated_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('tenant.automations.templates', [
            'templates' => $templates,
            'filter' => $filter,
            'filters' => MessageTemplateFilter::cases(),
            'search' => $search,
            'canManage' => $request->user()?->hasPermission(TenantPermission::AutomationsManage) ?? false,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::AutomationsManage), 403);

        return view('tenant.automations.template-form', $this->formData());
    }

    public function store(StoreMessageTemplateRequest $request): RedirectResponse
    {
        MessageTemplate::query()->create([
            ...$request->templateData(),
            'created_by_id' => $request->user()?->id,
        ]);

        return redirect()
            ->route('tenant.automations.templates')
            ->with('status', __('Template created.'));
    }

    public function edit(Request $request, MessageTemplate $template): View
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::AutomationsManage), 403);

        return view('tenant.automations.template-form', $this->formData($template));
    }

    public function update(
        UpdateMessageTemplateRequest $request,
        MessageTemplate $template,
    ): RedirectResponse {
        $template->update($request->templateData());

        return redirect()
            ->route('tenant.automations.templates.edit', $template)
            ->with('status', __('Template saved.'));
    }

    public function destroy(Request $request, MessageTemplate $template): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::AutomationsManage), 403);

        $template->delete();

        return redirect()
            ->route('tenant.automations.templates')
            ->with('status', __('Template deleted.'));
    }

    /**
     * @return array{
     *     template: ?MessageTemplate,
     *     channels: list<MessageTemplateChannel>,
     *     variableGroups: list<array{key: string, label: string, variables: list<array{key: string, label: string, token: string, sample: string}>}>,
     *     sampleValues: array<string, string>
     * }
     */
    private function formData(?MessageTemplate $template = null): array
    {
        return [
            'template' => $template,
            'channels' => MessageTemplateChannel::cases(),
            'variableGroups' => TemplateVariableCatalog::groups(),
            'sampleValues' => TemplateVariableCatalog::sampleValues(),
        ];
    }
}
