<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class WhatsAppWebController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->away($this->whatsappUrl());
    }

    private function whatsappUrl(): string
    {
        $phone = preg_replace('/\D+/', '', request()->string('phone')->toString()) ?: null;
        $message = request()->string('message')->toString();

        if ($phone === null || $phone === '') {
            return 'https://web.whatsapp.com/';
        }

        $url = 'https://web.whatsapp.com/send?phone='.$phone;

        if ($message !== '') {
            $url .= '&text='.rawurlencode($message);
        }

        return $url;
    }
}
