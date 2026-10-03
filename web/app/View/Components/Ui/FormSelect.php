<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\View\View;

class FormSelect extends Component
{
    /**
     * @param  array<int, array{value: string, label: string}|string>  $options
     */
    public function __construct(
        public string $name,
        public array $options = [],
        public ?string $value = null,
        public ?string $id = null,
        public string $placeholder = 'Select an option',
        public bool $required = false,
        public bool $disabled = false,
    ) {
        $this->id ??= $name;

        $normalized = $this->normalizedOptions();
        $this->value = $value ?? '';

        if ($this->value === '' && $normalized !== []) {
            $this->value = $normalized[0]['value'];
        }
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function normalizedOptions(): array
    {
        return collect($this->options)
            ->map(function (array|string $option): array {
                if (is_string($option)) {
                    return [
                        'value' => $option,
                        'label' => $option,
                    ];
                }

                return [
                    'value' => (string) ($option['value'] ?? ''),
                    'label' => (string) ($option['label'] ?? $option['value'] ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('components.ui.form-select');
    }
}
