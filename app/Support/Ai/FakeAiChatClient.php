<?php

namespace App\Support\Ai;

use App\Contracts\AiChatClient;

class FakeAiChatClient implements AiChatClient
{
    /** @var list<AiCompletion> */
    public array $queue = [];

    public function queueText(string $text): self
    {
        $this->queue[] = new AiCompletion(text: $text);

        return $this;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function queueTool(string $name, array $arguments): self
    {
        $this->queue[] = new AiCompletion(
            toolCalls: [new AiToolCall($name, $arguments)],
        );

        return $this;
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @param  list<array<string, mixed>>  $tools
     */
    public function complete(array $messages, array $tools): AiCompletion
    {
        if ($this->queue === []) {
            return new AiCompletion(
                text: 'Tell me a lead name and what you want to do — schedule a follow-up, book a site visit, or complete a task.',
            );
        }

        return array_shift($this->queue);
    }
}
