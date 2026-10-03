<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\InstallPlaybook;
use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutomationController extends Controller
{
    public function __invoke(Request $request, InstallPlaybook $installPlaybook): View
    {
        abort_unless($request->user()?->hasPermission(TenantPermission::AutomationsView), 403);

        /** @var array<string, array<string, mixed>> $definitions */
        $definitions = config('playbooks', []);

        $playbooks = collect($definitions)
            ->map(function (array $definition, string $key) use ($request, $installPlaybook): array {
                return [
                    'key' => $key,
                    'label' => $definition['label'] ?? $key,
                    'description' => $definition['description'] ?? '',
                    'enabled' => $installPlaybook->isEnabled($request->user(), $key),
                ];
            })
            ->values()
            ->all();

        return view('tenant.automations.index', [
            'playbooks' => $playbooks,
            'canManage' => $request->user()?->hasPermission(TenantPermission::AutomationsManage) ?? false,
        ]);
    }
}
