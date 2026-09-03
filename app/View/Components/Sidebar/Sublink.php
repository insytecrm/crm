<?php

namespace App\View\Components\Sidebar;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Sublink extends Component
{
    public function __construct(
        public string $href,
        public bool $active = false,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.sidebar.sublink');
    }
}
