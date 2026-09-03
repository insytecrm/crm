<?php

namespace App\View\Components\Ui\Tabs;

use Illuminate\View\Component;
use Illuminate\View\View;

class TabContent extends Component
{
    public string $default = '';

    public function __construct(public string $value) {}

    public function render(): View
    {
        return view('components.ui.tabs.content');
    }
}
