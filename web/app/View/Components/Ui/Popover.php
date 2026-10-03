<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\View\View;

class Popover extends Component
{
    /**
     * @param  'top'|'right'|'bottom'|'left'  $side
     * @param  'start'|'center'|'end'  $align
     */
    public function __construct(
        public string $side = 'bottom',
        public string $align = 'center',
        public string $width = '72',
        public string $contentClass = 'p-4',
        public bool $show = false,
        public bool $closeOnContentClick = false,
        public string $variant = 'default',
    ) {}

    public function panelClass(): string
    {
        return match ($this->variant) {
            'sidebar' => 'border-sidebar-border bg-sidebar-accent text-white shadow-sm shadow-slate-900/5',
            default => 'border-slate-200 bg-white text-black shadow-sm shadow-slate-900/5',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function config(): array
    {
        return [
            'open' => $this->show,
        ];
    }

    public function panelPositionClass(): string
    {
        $side = match ($this->side) {
            'top' => 'bottom-full mb-2',
            'left' => 'end-full me-2',
            'right' => 'start-full ms-2',
            default => 'top-full mt-2',
        };

        $align = match ($this->side) {
            'left', 'right' => match ($this->align) {
                'start' => 'top-0',
                'end' => 'bottom-0',
                default => 'top-1/2 -translate-y-1/2',
            },
            default => match ($this->align) {
                'start' => 'start-0',
                'end' => 'end-0',
                default => 'start-1/2 -translate-x-1/2',
            },
        };

        return 'absolute z-[70] '.$side.' '.$align;
    }

    public function originClass(): string
    {
        return match ($this->side) {
            'top' => match ($this->align) {
                'start' => 'origin-bottom-left',
                'end' => 'origin-bottom-right',
                default => 'origin-bottom',
            },
            'left' => match ($this->align) {
                'start' => 'origin-top-right',
                'end' => 'origin-bottom-right',
                default => 'origin-right',
            },
            'right' => match ($this->align) {
                'start' => 'origin-top-left',
                'end' => 'origin-bottom-left',
                default => 'origin-left',
            },
            default => match ($this->align) {
                'start' => 'origin-top-left',
                'end' => 'origin-top-right',
                default => 'origin-top',
            },
        };
    }

    public function widthClass(): string
    {
        return match ($this->width) {
            'auto' => 'w-auto',
            '48' => 'w-48',
            '56' => 'w-56',
            '64' => 'w-64',
            '72' => 'w-72',
            '80' => 'w-80',
            '96' => 'w-96',
            default => $this->width,
        };
    }

    public function render(): View
    {
        return view('components.ui.popover');
    }
}
