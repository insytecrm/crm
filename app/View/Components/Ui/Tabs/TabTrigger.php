<?php

namespace App\View\Components\Ui\Tabs;

use Illuminate\View\Component;
use Illuminate\View\View;

class TabTrigger extends Component
{
    /** @var 'default'|'line' */
    public string $variant = 'default';

    public function __construct(public string $value) {}

    public function triggerClass(): string
    {
        $base = 'relative inline-flex items-center justify-center whitespace-nowrap text-sm transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy/50 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50';

        if ($this->variant === 'line') {
            return $base.' h-8 gap-1.5 rounded-md border border-transparent px-3 font-medium text-slate-500 hover:text-black after:absolute after:inset-x-0 after:bottom-[-1px] after:h-0.5 after:bg-navy after:opacity-0 after:transition-opacity';
        }

        return $base.' z-[1] h-7 rounded-[10px] px-4 font-medium text-slate-500 hover:text-slate-700';
    }

    public function activeClass(): string
    {
        if ($this->variant === 'line') {
            return 'text-black after:opacity-100';
        }

        return 'font-semibold text-slate-900';
    }

    public function render(): View
    {
        return view('components.ui.tabs.trigger');
    }
}
