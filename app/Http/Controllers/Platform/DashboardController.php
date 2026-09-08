<?php

namespace App\Http\Controllers\Platform;

use App\Contracts\PlatformDashboardData;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the platform dashboard.
     */
    public function __invoke(PlatformDashboardData $dashboardData): View
    {
        $hour = now()->hour;

        $greeting = match (true) {
            $hour < 12 => __('Good morning'),
            $hour < 17 => __('Good afternoon'),
            default => __('Good evening'),
        };

        return view('platform.dashboard', [
            'greeting' => $greeting,
            'userName' => auth()->user()->name,
            'contextDate' => now()->timezone(config('app.timezone'))->format('l, M j · g:i A'),
            'dashboard' => $dashboardData->get(),
        ]);
    }
}
