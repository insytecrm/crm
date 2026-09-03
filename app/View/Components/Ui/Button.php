<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\View\View;

class Button extends Component
{
    /**
     * @param  'default'|'destructive'|'success'|'outline'|'secondary'|'soft'|'ghost'|'link'  $variant
     * @param  'default'|'sm'|'lg'|'icon'  $size
     */
    public function __construct(
        public string $variant = 'default',
        public string $size = 'default',
        public ?string $href = null,
        public string $type = 'button',
    ) {}

    /**
     * @return array<string, string>
     */
    public function classes(): array
    {
        $base = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg text-sm font-semibold transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:size-4 [&_svg]:shrink-0';

        $variants = [
            'default' => 'bg-navy text-white shadow-sm hover:bg-navy/90 hover:shadow',
            'destructive' => 'bg-red-600 text-white shadow-sm hover:bg-red-700 hover:shadow',
            'success' => 'bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 hover:shadow',
            'outline' => 'border border-slate-200 bg-white text-black shadow-sm hover:bg-slate-50',
            'secondary' => 'bg-slate-100 text-black hover:bg-slate-200',
            'soft' => 'border border-navy/20 bg-navy/10 text-navy shadow-sm hover:bg-navy/15',
            'ghost' => 'text-black hover:bg-slate-100',
            'link' => 'h-auto rounded-none p-0 font-medium text-black underline-offset-4 hover:underline',
        ];

        $sizes = [
            'default' => 'h-9 px-5 py-2',
            'sm' => 'h-8 px-3.5 text-xs',
            'lg' => 'h-11 px-8 text-base',
            'icon' => 'size-9 p-0',
        ];

        $variantClasses = $variants[$this->variant] ?? $variants['default'];
        $sizeClasses = $this->variant === 'link' ? '' : ($sizes[$this->size] ?? $sizes['default']);

        return [
            'tag' => $this->href ? 'a' : 'button',
            'classes' => trim($base.' '.$variantClasses.' '.$sizeClasses),
        ];
    }

    public function render(): View
    {
        return view('components.ui.button');
    }
}
