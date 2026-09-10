<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class AutomationController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->route('tenant.automations.workflows');
    }
}
