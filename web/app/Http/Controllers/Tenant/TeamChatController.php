<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\FindOrCreateDirectConversation;
use App\Actions\StoreTeamMessageAttachments;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTeamMessageRequest;
use App\Models\TeamConversation;
use App\Models\TeamMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeamChatController extends Controller
{
    public function bootstrap(): JsonResponse
    {
        $userId = auth()->id();

        $users = User::query()
            ->whereKeyNot($userId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
            ])
            ->values();

        $conversations = TeamConversation::query()
            ->whereHas('participants', fn ($query) => $query->where('users.id', $userId))
            ->with([
                'participants' => fn ($query) => $query->whereKeyNot($userId),
                'messages' => fn ($query) => $query->latest('id')->limit(1)->with('user:id,name'),
            ])
            ->withMax('messages', 'created_at')
            ->orderByDesc('messages_max_created_at')
            ->orderByDesc('id')
            ->get()
            ->map(function (TeamConversation $conversation) use ($userId): array {
                $participant = $conversation->participants->first();
                $lastMessage = $conversation->messages->first();

                return [
                    'id' => $conversation->id,
                    'participant' => $participant ? [
                        'id' => $participant->id,
                        'name' => $participant->name,
                    ] : null,
                    'last_message' => $lastMessage ? $this->formatMessage($lastMessage, $userId) : null,
                ];
            })
            ->filter(fn (array $conversation): bool => $conversation['participant'] !== null)
            ->values();

        return response()->json([
            'current_user' => [
                'id' => $userId,
                'name' => auth()->user()->name,
            ],
            'users' => $users,
            'conversations' => $conversations,
        ]);
    }

    public function messages(TeamConversation $conversation): JsonResponse
    {
        $this->ensureParticipant($conversation);

        $messages = $conversation->messages()
            ->with('user:id,name')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->map(fn (TeamMessage $message): array => $this->formatMessage($message, auth()->id()));

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function store(
        StoreTeamMessageRequest $request,
        FindOrCreateDirectConversation $findOrCreateDirectConversation,
        StoreTeamMessageAttachments $storeTeamMessageAttachments,
    ): JsonResponse {
        $conversation = $findOrCreateDirectConversation->handle(
            auth()->id(),
            $request->integer('recipient_id'),
        );

        $attachments = $storeTeamMessageAttachments->handle(
            $conversation,
            $request->file('attachments'),
        );

        $message = $conversation->messages()->create([
            'user_id' => auth()->id(),
            'body' => $request->validated('body'),
            'attachments' => $attachments,
        ]);

        $message->load('user:id,name');

        return response()->json([
            'conversation_id' => $conversation->id,
            'message' => $this->formatMessage($message, auth()->id()),
        ], 201);
    }

    public function downloadAttachment(TeamMessage $message, string $attachment): StreamedResponse
    {
        $this->ensureParticipant($message->conversation);

        $file = collect($message->attachments ?? [])
            ->firstWhere('id', $attachment);

        abort_unless(is_array($file) && isset($file['path'], $file['disk'], $file['name']), 404);

        return Storage::disk($file['disk'])->download($file['path'], $file['name']);
    }

    private function ensureParticipant(TeamConversation $conversation): void
    {
        abort_unless($conversation->includesUser(auth()->id()), 403);
    }

    /**
     * @return array{
     *     id: int,
     *     sender_id: int,
     *     sender_name: string,
     *     body: string|null,
     *     attachments: array<int, array{id: string, name: string, url: string, mime_type: string|null, size: int}>,
     *     created_at: string,
     *     is_mine: bool
     * }
     */
    private function formatMessage(TeamMessage $message, int $viewerId): array
    {
        return [
            'id' => $message->id,
            'sender_id' => $message->user_id,
            'sender_name' => $message->user?->name ?? __('Unknown'),
            'body' => $message->body,
            'attachments' => collect($message->attachments ?? [])
                ->map(fn (array $attachment): array => [
                    'id' => $attachment['id'],
                    'name' => $attachment['name'],
                    'url' => route('tenant.team-chat.attachments.download', [
                        'message' => $message->id,
                        'attachment' => $attachment['id'],
                    ], false),
                    'mime_type' => $attachment['mime_type'] ?? null,
                    'size' => $attachment['size'] ?? 0,
                ])
                ->values()
                ->all(),
            'created_at' => $message->created_at?->toIso8601String() ?? now()->toIso8601String(),
            'is_mine' => $message->user_id === $viewerId,
        ];
    }
}
