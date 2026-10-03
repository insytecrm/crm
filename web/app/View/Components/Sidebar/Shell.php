<?php

namespace App\View\Components\Sidebar;

use Illuminate\View\Component;
use Illuminate\View\View;

class Shell extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $contextLabel = null,
        public ?string $contextBadge = null,
        public ?string $logoutAction = null,
        public ?string $profileHref = null,
        public bool $fullBleed = false,
    ) {}

    public function render(): View
    {
        return view('components.sidebar.shell');
    }
}
