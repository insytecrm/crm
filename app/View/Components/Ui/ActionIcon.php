<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\View\View;

class ActionIcon extends Component
{
    public function __construct(
        public string $icon = 'view',
        public bool $compact = true,
        public ?string $size = null,
        public ?string $href = null,
        public string $type = 'button',
        public ?string $title = null,
    ) {}

    public function resolvedSize(): string
    {
        if ($this->size !== null) {
            return $this->size;
        }

        return $this->compact ? 'sm' : 'md';
    }

    public function buttonClasses(): string
    {
        $base = 'inline-flex shrink-0 items-center justify-center transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2';

        $sizes = [
            'xs' => 'size-7 rounded-[10px] shadow-[0_2px_6px_rgba(0,0,0,0.14)]',
            'sm' => 'size-8 rounded-[12px] shadow-[0_2px_8px_rgba(0,0,0,0.15)]',
            'md' => 'size-10 rounded-2xl shadow-[0_3px_10px_rgba(0,0,0,0.16)]',
        ];

        $size = $sizes[$this->resolvedSize()] ?? $sizes['sm'];

        $variants = [
            'whatsapp' => 'bg-[#25D366] text-white hover:bg-[#20bd5a] focus-visible:ring-[#25D366]',
            'call' => 'bg-[#34C759] text-white hover:bg-[#2db84e] focus-visible:ring-[#34C759]',
            'follow-up' => 'bg-[#5AC8FA] text-white hover:bg-[#47b8f0] focus-visible:ring-[#5AC8FA]',
            'reminder' => 'bg-[#5AC8FA] text-white hover:bg-[#47b8f0] focus-visible:ring-[#5AC8FA]',
            'reschedule' => 'bg-[#5AC8FA] text-white hover:bg-[#47b8f0] focus-visible:ring-[#5AC8FA]',
            'complete' => 'bg-[#34C759] text-white hover:bg-[#2db84e] focus-visible:ring-[#34C759]',
            'cancel' => 'bg-[#FF9500] text-white hover:bg-[#e68600] focus-visible:ring-[#FF9500]',
            'site-visit' => 'bg-[#FF9500] text-white hover:bg-[#e68600] focus-visible:ring-[#FF9500]',
            'location' => 'bg-[#FF9500] text-white hover:bg-[#e68600] focus-visible:ring-[#FF9500]',
            'booking' => 'bg-[#5856D6] text-white hover:bg-[#4a48c4] focus-visible:ring-[#5856D6]',
            'view' => 'bg-slate-500 text-white hover:bg-slate-600 focus-visible:ring-slate-500',
            'edit' => 'bg-[#007AFF] text-white hover:bg-[#0066d6] focus-visible:ring-[#007AFF]',
            'delete' => 'bg-[#FF9500] text-white hover:bg-[#e68600] focus-visible:ring-[#FF9500]',
            'archive' => 'bg-[#FF9500] text-white hover:bg-[#e68600] focus-visible:ring-[#FF9500]',
            'download' => 'bg-[#5AC8FA] text-white hover:bg-[#47b8f0] focus-visible:ring-[#5AC8FA]',
            'invoice' => 'bg-[#34C759] text-white hover:bg-[#2db84e] focus-visible:ring-[#34C759]',
            'agreement' => 'bg-[#5856D6] text-white hover:bg-[#4a48c4] focus-visible:ring-[#5856D6]',
            'play' => 'bg-[#5AC8FA] text-white hover:bg-[#47b8f0] focus-visible:ring-[#5AC8FA]',
        ];

        $variant = $variants[$this->icon] ?? $variants['view'];

        return trim("{$base} {$size} {$variant}");
    }

    public function iconClasses(): string
    {
        return match ($this->resolvedSize()) {
            'xs' => 'size-3.5',
            'sm' => 'size-4',
            'md' => 'size-5',
            default => 'size-4',
        };
    }

    public function tag(): string
    {
        return $this->href ? 'a' : 'button';
    }

    public function render(): View
    {
        return view('components.ui.action-icon');
    }
}
