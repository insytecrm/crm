<?php

namespace App\Support\Ai;

class AiCompletion
{
    /**
     * @param  list<AiToolCall>  $toolCalls
     */
    public function __construct(
        public string $text = '',
        public array $toolCalls = [],
    ) {}

    public function hasToolCalls(): bool
    {
        return $this->toolCalls !== [];
    }
}
