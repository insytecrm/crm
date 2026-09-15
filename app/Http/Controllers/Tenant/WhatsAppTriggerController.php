<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class WhatsAppTriggerController extends Controller
{
    public function index(): View
    {
        return view('tenant.whatsapp-triggers.index');
    }
}
