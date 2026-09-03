<?php

namespace App\View\Components\Ui\Tabs;

use Illuminate\View\Component;
use Illuminate\View\View;

class TabList extends Component
{
    /** @var 'default'|'line' */
    public string $variant = 'default';

    public function listClass(): string
    {
        $base = 'group/tabs-list inline-flex w-fit items-center';

        return match ($this->variant) {
            'line' => $base.' gap-1 border-b border-slate-100 text-muted-foreground',
            default => $base.' relative h-9 justify-center gap-0 rounded-full bg-slate-100 p-1',
        };
    }

    public function render(): View
    {
        return view('components.ui.tabs.list');
    }
}
