<?php

use App\Enums\InsyteAiTool;
use App\Support\Ai\OpenAiChatClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

test('openai request sends function tools with object properties', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.openai.com/v1/responses' => Http::response([
            'output_text' => 'Hello',
            'output' => [],
        ], 200),
    ]);

    config([
        'services.openai.key' => 'sk-test',
        'services.openai.model' => 'gpt-4o',
    ]);

    $completion = (new OpenAiChatClient)->complete(
        [['role' => 'user', 'content' => 'Hi']],
        InsyteAiTool::definitions(),
    );

    expect($completion->text)->toBe('Hello');

    Http::assertSent(function ($request): bool {
        $payload = json_decode($request->body());
        $listToday = collect($payload->tools)->firstWhere('name', 'list_today');

        return $request->url() === 'https://api.openai.com/v1/responses'
            && is_object($listToday?->parameters?->properties)
            && $listToday->strict === false
            && $payload->input[0]->type === 'message'
            && $payload->input[0]->role === 'user'
            && $payload->input[0]->content[0]->type === 'input_text';
    });
});

test('openai request puts system text in instructions and assistant text as output_text', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.openai.com/v1/responses' => Http::response([
            'output_text' => 'Done',
            'output' => [],
        ], 200),
    ]);

    config([
        'services.openai.key' => 'sk-test',
        'services.openai.model' => 'gpt-4o',
    ]);

    (new OpenAiChatClient)->complete(
        [
            ['role' => 'system', 'content' => 'You are InSyte AI OS.'],
            ['role' => 'user', 'content' => 'Find Rahul'],
            ['role' => 'assistant', 'content' => 'Searching now.'],
        ],
        [],
    );

    Http::assertSent(function ($request): bool {
        $payload = json_decode($request->body());

        return $payload->instructions === 'You are InSyte AI OS.'
            && $payload->input[0]->role === 'user'
            && $payload->input[0]->content[0]->type === 'input_text'
            && $payload->input[1]->role === 'assistant'
            && $payload->input[1]->content[0]->type === 'output_text';
    });
});

test('openai client returns the unavailable message when the api returns 400', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.openai.com/v1/responses' => Http::response([
            'error' => ['message' => 'Invalid schema for function list_today'],
        ], 400),
    ]);

    config([
        'services.openai.key' => 'sk-test',
        'services.openai.model' => 'gpt-4o',
    ]);

    $completion = (new OpenAiChatClient)->complete(
        [['role' => 'user', 'content' => 'Hi']],
        InsyteAiTool::definitions(),
    );

    expect($completion->text)->toBe('InSyte AI is unavailable right now. Please try again.');
});
