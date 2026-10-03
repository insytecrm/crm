<?php

namespace App\Contracts;

use App\Support\Ai\AiCompletion;

interface AiChatClient
{
    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @param  list<array<string, mixed>>  $tools
     */
    public function complete(array $messages, array $tools): AiCompletion;
}
