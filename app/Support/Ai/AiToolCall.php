<?php

namespace App\Support\Ai;

class AiToolCall
{
    /**
     * @param  array<string, mixed>  $arguments
     */
    public function __construct(
        public string $name,
        public array $arguments = [],
    ) {}
}
