<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SitePageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $page = (string) ($request->route('page') ?? 'home');
        $slug = $request->route('slug');

        return view('welcome', [
            'page' => $page,
            'slug' => is_string($slug) ? $slug : null,
            'title' => $this->titleFor($page, is_string($slug) ? $slug : null),
        ]);
    }

    private function titleFor(string $page, ?string $slug): string
    {
        $titles = [
            'home' => 'InSyte CRM — Turn Property Enquiries into Bookings',
            'crm' => 'CRM | InSyte',
            'automation' => 'Automation | InSyte',
            'ai' => 'InSyte AI OS | InSyte',
            'integrations' => 'Integrations | InSyte',
            'customization' => 'Customization | InSyte',
            'solutions-real-estate' => 'Real Estate CRM | InSyte',
            'solutions-sales-teams' => 'Sales Teams | InSyte',
            'solutions-small-businesses' => 'Small Businesses | InSyte',
            'solutions-agencies' => 'Agencies | InSyte',
            'solutions-other' => 'Other Industries | InSyte',
            'pricing' => 'Pricing | InSyte',
            'demo' => 'Book a Demo | InSyte',
            'signup' => 'Start Free | InSyte',
            'faqs' => 'FAQs | InSyte',
            'help' => $slug ? 'Help | InSyte' : 'Help Center | InSyte',
            'docs' => $slug ? 'Docs | InSyte' : 'Documentation | InSyte',
            'guides' => $slug ? 'Guide | InSyte' : 'Guides | InSyte',
            'blog' => 'Blog | InSyte',
            'about' => 'About | InSyte',
            'contact' => 'Contact | InSyte',
            'careers' => 'Careers | InSyte',
        ];

        return $titles[$page] ?? 'InSyte CRM';
    }
}
