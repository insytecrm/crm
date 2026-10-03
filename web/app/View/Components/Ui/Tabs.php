<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\View\View;

class Tabs extends Component
{
    /**
     * @param  'default'|'line'  $variant
     */
    public function __construct(
        public string $default = '',
        public string $variant = 'default',
    ) {}

    public function render(): View
    {
        return view('components.ui.tabs');
    }
}
