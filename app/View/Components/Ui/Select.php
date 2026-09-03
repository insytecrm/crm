<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\View\View;

class Select extends Component
{
    /**
     * @param  array<int, array{value: string, label: string}>  $options
     */
    public function __construct(
        public string $name,
        public array $options = [],
        public ?string $value = null,
        public ?string $id = null,
        public string $placeholder = 'Select an option',
        public bool $required = false,
        public bool $disabled = false,
        public bool $submitOnSelect = false,
        public ?string $triggerClass = null,
        public ?string $ariaLabel = null,
        public int $minMenuWidth = 300,
        public bool $portal = true,
    ) {
        $this->id ??= $name;
        $this->value ??= '';
    }

    /**
     * @return array<string, mixed>
     */
    public function config(): array
    {
        return [
            'options' => $this->options,
            'value' => (string) $this->value,
            'placeholder' => $this->placeholder,
            'submitOnSelect' => $this->submitOnSelect,
            'minMenuWidth' => $this->minMenuWidth,
            'portal' => $this->portal,
            'disabled' => $this->disabled,
        ];
    }

    public function triggerClasses(): string
    {
        if ($this->triggerClass !== null) {
            return $this->triggerClass;
        }

        return 'flex h-10 w-full items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-left text-sm shadow-sm transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';
    }

    public function render(): View
    {
        return view('components.ui.select');
    }
}
