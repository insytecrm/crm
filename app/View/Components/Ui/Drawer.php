<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\View\View;

class Drawer extends Component
{
    /**
     * @param  'left'|'right'|'bottom'  $side
     * @param  'viewport'|'content'  $inset
     */
    public function __construct(
        public ?string $name = null,
        public bool $show = false,
        public string $side = 'right',
        public string $maxWidth = '5xl',
        public string $inset = 'viewport',
        public ?string $closeUrl = null,
        public bool $closeOnOverlay = true,
        public bool $closeOnEscape = true,
        public bool $overlayOnly = false,
        public bool $teleport = true,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function config(): array
    {
        return [
            'name' => $this->name,
            'show' => $this->show,
            'side' => $this->side,
            'closeUrl' => $this->closeUrl,
            'closeOnOverlay' => $this->closeOnOverlay,
            'closeOnEscape' => $this->closeOnEscape,
            'overlayOnly' => $this->overlayOnly,
            'teleport' => $this->teleport,
        ];
    }

    public function maxWidthClass(): string
    {
        if ($this->side === 'left') {
            return match ($this->maxWidth) {
                'sm' => 'w-64 max-w-[85vw]',
                'md' => 'w-72 max-w-[85vw]',
                default => 'w-64 max-w-[85vw]',
            };
        }

        if ($this->side === 'bottom') {
            return 'w-full max-h-[85vh]';
        }

        return match ($this->maxWidth) {
            'sm' => 'w-full max-w-sm',
            'md' => 'w-full max-w-md',
            'lg' => 'w-full max-w-lg',
            'xl' => 'w-full max-w-xl',
            '2xl' => 'w-full max-w-2xl',
            '3xl' => 'w-full max-w-3xl',
            '4xl' => 'w-full max-w-4xl',
            '5xl' => 'w-full max-w-5xl',
            'half' => 'w-full lg:w-[calc((100vw-var(--sidebar-width,16rem))/2)] lg:max-w-[calc((100vw-var(--sidebar-width,16rem))/2)]',
            'full' => 'w-full max-w-full',
            default => 'w-full max-w-5xl',
        };
    }

    public function overlayPositionClass(): string
    {
        if ($this->inset === 'content') {
            return 'top-14 bottom-0 start-0 end-0 lg:start-[var(--sidebar-width,16rem)]';
        }

        return 'inset-0';
    }

    public function panelPositionClass(): string
    {
        if ($this->inset === 'content') {
            return match ($this->side) {
                'left' => 'top-14 bottom-0 start-0 lg:start-[var(--sidebar-width,16rem)]',
                'bottom' => 'start-0 end-0 bottom-0 top-auto lg:start-[var(--sidebar-width,16rem)]',
                default => 'top-14 bottom-0 end-0',
            };
        }

        return match ($this->side) {
            'left' => 'inset-y-0 start-0',
            'bottom' => 'inset-x-0 bottom-0',
            default => 'inset-y-0 end-0',
        };
    }

    public function render(): View
    {
        return view('components.ui.drawer');
    }
}
