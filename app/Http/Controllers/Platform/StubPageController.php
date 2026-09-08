<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StubPageController extends Controller
{
    /**
     * Display a platform section stub.
     */
    public function __invoke(): View
    {
        return view('platform.stub', $this->page(request()->route()?->getName()));
    }

    /**
     * @return array{title: string, description: string}
     */
    private function page(?string $routeName): array
    {
        return match ($routeName) {
            'platform.integrations' => [
                'title' => __('Integrations'),
                'description' => __('Connect platform-level services and partner integrations.'),
            ],
            'platform.analytics' => [
                'title' => __('Analytics'),
                'description' => __('Review platform-wide performance and partner analytics.'),
            ],
            'platform.utilities' => [
                'title' => __('Utilities'),
                'description' => __('Platform tools and maintenance utilities.'),
            ],
            'platform.settings' => [
                'title' => __('Settings'),
                'description' => __('Configure platform preferences and your admin account.'),
            ],
            default => throw new NotFoundHttpException,
        };
    }
}
