<?php

namespace App\Support\Ai;

use App\Contracts\AiChatClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenAiChatClient implements AiChatClient
{
    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @param  list<array<string, mixed>>  $tools
     */
    public function complete(array $messages, array $tools): AiCompletion
    {
        $key = config('services.openai.key');

        if (! is_string($key) || $key === '') {
            return new AiCompletion(
                text: __('InSyte AI OS is not configured yet. Add OPENAI_API_KEY in the environment and try again.'),
            );
        }

        $payload = $this->payloadFromMessages($messages);

        if ($tools !== []) {
            $payload['tools'] = $tools;
        }

        try {
            $response = Http::withToken($key)
                ->acceptJson()
                ->asJson()
                ->connectTimeout(5)
                ->timeout(45)
                ->post('https://api.openai.com/v1/responses', $payload);
        } catch (ConnectionException $exception) {
            Log::warning('InSyte AI OS could not reach OpenAI.', [
                'message' => $exception->getMessage(),
            ]);

            return new AiCompletion(
                text: __('InSyte AI is unavailable right now. Please try again.'),
            );
        }

        if (! $response->successful()) {
            Log::warning('InSyte AI OS received an OpenAI error.', [
                'status' => $response->status(),
                'error' => $response->json('error.message') ?? $response->body(),
            ]);

            return new AiCompletion(
                text: __('InSyte AI is unavailable right now. Please try again.'),
            );
        }

        try {
            return $this->parse($response->json() ?? []);
        } catch (Throwable $exception) {
            Log::warning('InSyte AI OS could not parse the OpenAI response.', [
                'message' => $exception->getMessage(),
            ]);

            return new AiCompletion(
                text: __('InSyte AI is unavailable right now. Please try again.'),
            );
        }
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array{model: mixed, store: false, input: list<array{type: string, role: string, content: list<array{type: string, text: string}>}>, instructions?: string}
     */
    private function payloadFromMessages(array $messages): array
    {
        $instructions = [];
        $input = [];

        foreach ($messages as $message) {
            $role = $message['role'];

            if (in_array($role, ['system', 'developer'], true)) {
                $instructions[] = $message['content'];

                continue;
            }

            $input[] = [
                'type' => 'message',
                'role' => $role === 'assistant' ? 'assistant' : 'user',
                'content' => [
                    [
                        'type' => $role === 'assistant' ? 'output_text' : 'input_text',
                        'text' => $message['content'],
                    ],
                ],
            ];
        }

        $payload = [
            'model' => config('services.openai.model'),
            'store' => false,
            'input' => $input,
        ];

        if ($instructions !== []) {
            $payload['instructions'] = implode("\n\n", $instructions);
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function parse(array $payload): AiCompletion
    {
        $toolCalls = [];

        foreach ($payload['output'] ?? [] as $item) {
            if (! is_array($item) || ($item['type'] ?? null) !== 'function_call') {
                continue;
            }

            $name = $item['name'] ?? null;
            $arguments = $item['arguments'] ?? '{}';

            if (! is_string($name) || $name === '') {
                continue;
            }

            $decoded = is_string($arguments)
                ? json_decode($arguments, true)
                : $arguments;

            $toolCalls[] = new AiToolCall(
                $name,
                is_array($decoded) ? $decoded : [],
            );
        }

        $text = $payload['output_text'] ?? '';

        if (! is_string($text) || $text === '') {
            $text = $this->plainTextFromOutput($payload['output'] ?? []);
        }

        return new AiCompletion(
            text: is_string($text) ? $text : '',
            toolCalls: $toolCalls,
        );
    }

    private function plainTextFromOutput(mixed $output): string
    {
        if (! is_array($output)) {
            return '';
        }

        foreach ($output as $item) {
            if (! is_array($item) || ($item['type'] ?? null) !== 'message') {
                continue;
            }

            foreach ($item['content'] ?? [] as $content) {
                if (! is_array($content)) {
                    continue;
                }

                if (in_array($content['type'] ?? null, ['output_text', 'text'], true) && is_string($content['text'] ?? null)) {
                    return $content['text'];
                }
            }
        }

        return '';
    }
}
