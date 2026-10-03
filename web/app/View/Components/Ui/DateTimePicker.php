<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\View\View;

class DateTimePicker extends Component
{
    public function __construct(

        public string $name,

        public string $mode = 'datetime',

        public ?string $value = null,

        public ?string $id = null,

        public bool $required = false,

        public bool $disabled = false,

    ) {

        $this->id ??= $name;

        $this->value ??= '';

    }

    public function inputType(): string
    {

        return $this->mode === 'date' ? 'date' : 'datetime-local';

    }

    public function normalizedValue(): string
    {

        if ($this->value === '') {

            return '';

        }

        if ($this->mode === 'date') {

            return substr($this->value, 0, 10);

        }

        $normalized = $this->value;

        if (str_contains($normalized, ' ')) {

            $normalized = str_replace(' ', 'T', $normalized);

        }

        return substr($normalized, 0, 16);

    }

    public function render(): View
    {

        return view('components.ui.datetime-picker');

    }

}
