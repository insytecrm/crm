<?php

namespace App\View\Components;

use App\Queries\TenantNavIndicators;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class TenantLayout extends Component
{
    public function __construct(
        public ?string $title = null,
        public bool $fullBleed = false,
    ) {}

    public function render(): View
    {
        $navIndicators = null;

        if (Auth::check() && tenant() !== null) {
            $navIndicators = app(TenantNavIndicators::class)->forTenant();
        }

        return view('layouts.tenant', [
            'navIndicators' => $navIndicators,
        ]);
    }
}
