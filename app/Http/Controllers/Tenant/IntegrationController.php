<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\SettingsTab;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class IntegrationController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->route('tenant.settings.index', [
            'tab' => SettingsTab::Integrations->value,
        ]);
    }
}
