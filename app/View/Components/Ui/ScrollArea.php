<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\View\View;

class ScrollArea extends Component
{
    /**
     * @param  'vertical'|'horizontal'|'both'  $orientation
     */
    public function __construct(
        public string $orientation = 'vertical',
        public bool $fade = false,
        public bool $hideScrollbar = false,
        public bool $gutter = true,
    ) {}

    public function rootClasses(): string
    {
        return 'relative overflow-hidden';
    }

    public function viewportClasses(): string
    {
        $classes = [
            'ui-scroll-area-viewport',
            'size-full min-h-0 min-w-0 rounded-[inherit] outline-none transition-[color,box-shadow]',
            'focus-visible:outline-1 focus-visible:ring-[3px] focus-visible:ring-ring/50',
            'overscroll-contain',
        ];

        if ($this->gutter) {
            $classes[] = 'scrollbar-gutter-stable';
        }

        if ($this->fade) {
            $classes[] = match ($this->orientation) {
                'horizontal' => 'scroll-fade-x',
                'both' => 'scroll-fade',
                default => 'scroll-fade-y',
            };
        }

        if ($this->hideScrollbar) {
            $classes[] = 'no-scrollbar';
        }

        $classes[] = match ($this->orientation) {
            'horizontal' => 'overflow-x-auto overflow-y-hidden',
            'both' => 'overflow-auto',
            default => 'overflow-y-auto overflow-x-hidden',
        };

        return implode(' ', $classes);
    }

    public function render(): View
    {
        return view('components.ui.scroll-area');
    }
}
