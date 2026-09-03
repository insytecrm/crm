<?php

use App\Models\TeamConversation;
use App\Models\TeamMessage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('tenant layout renders team inbox header actions and drawer root', function () {
    createTestTenant();
    actingAsTenantUser();

    $this->get('/acme/dashboard')
        ->assertOk()
        ->assertSee('Team inbox', false)
        ->assertSee('Quick add', false)
        ->assertSee('id="team-inbox-drawer-root"', false);
});

test('team chat bootstrap returns team members and conversations', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $member = User::query()->create([
        'name' => 'Sales Rep',
        'email' => 'sales@acme.test',
        'password' => bcrypt('password'),
    ]);

    $conversation = TeamConversation::factory()->create();
    $conversation->participants()->attach([$admin->id, $member->id]);

    TeamMessage::factory()->create([
        'team_conversation_id' => $conversation->id,
        'user_id' => $member->id,
        'body' => 'Need help with a lead',
    ]);

    $response = $this->getJson('/acme/team-chat/bootstrap')
        ->assertOk()
        ->assertJsonPath('current_user.id', $admin->id)
        ->assertJsonPath('users.0.name', 'Sales Rep')
        ->assertJsonPath('conversations.0.participant.name', 'Sales Rep')
        ->assertJsonPath('conversations.0.last_message.body', 'Need help with a lead');

    expect($response->json('users'))->toHaveCount(1)
        ->and($response->json('conversations'))->toHaveCount(1);
});

test('tenant users can send team chat messages with attachments', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $member = User::query()->create([
        'name' => 'Ops User',
        'email' => 'ops@acme.test',
        'password' => bcrypt('password'),
    ]);

    Storage::fake('local');

    $file = UploadedFile::fake()->create('brief.pdf', 120, 'application/pdf');

    $response = $this->postJson('/acme/team-chat/messages', [
        'recipient_id' => $member->id,
        'body' => 'Please review this file.',
        'attachments' => [$file],
    ])
        ->assertCreated()
        ->assertJsonPath('message.body', 'Please review this file.')
        ->assertJsonPath('message.is_mine', true);

    $conversationId = $response->json('conversation_id');

    expect(TeamConversation::query()->count())->toBe(1)
        ->and(TeamMessage::query()->count())->toBe(1);

    $this->getJson("/acme/team-chat/conversations/{$conversationId}/messages")
        ->assertOk()
        ->assertJsonPath('messages.0.body', 'Please review this file.')
        ->assertJsonPath('messages.0.attachments.0.name', 'brief.pdf');

    $message = TeamMessage::query()->firstOrFail();
    $attachmentId = $message->attachments[0]['id'];

    $this->get("/acme/team-chat/messages/{$message->id}/attachments/{$attachmentId}/download")
        ->assertOk();
});

test('users cannot read conversations they do not belong to', function () {
    createTestTenant();
    actingAsTenantUser();

    $otherUsers = collect([
        User::query()->create([
            'name' => 'User One',
            'email' => 'one@acme.test',
            'password' => bcrypt('password'),
        ]),
        User::query()->create([
            'name' => 'User Two',
            'email' => 'two@acme.test',
            'password' => bcrypt('password'),
        ]),
    ]);
    $conversation = TeamConversation::factory()->create();
    $conversation->participants()->attach($otherUsers->pluck('id'));

    $this->getJson("/acme/team-chat/conversations/{$conversation->id}/messages")
        ->assertForbidden();
});

test('users cannot message themselves', function () {
    createTestTenant();
    $admin = actingAsTenantUser();

    $this->postJson('/acme/team-chat/messages', [
        'recipient_id' => $admin->id,
        'body' => 'Hello me',
    ])->assertUnprocessable();
});
