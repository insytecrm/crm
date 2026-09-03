<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function __invoke(): View
    {
        return view('tenant.integrations.index');
    }
}
