<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\View\View;

class Combobox extends Component
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
        public bool $searchable = true,
        public bool $required = false,
        public bool $submitOnSelect = false,
        public bool $disabled = false,
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
            'searchable' => $this->searchable,
            'submitOnSelect' => $this->submitOnSelect,
        ];
    }

    public function render(): View
    {
        return view('components.ui.combobox');
    }
}
