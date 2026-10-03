<?php

namespace App\Actions;

use App\Contracts\AiChatClient;
use App\Enums\AiConversationStatus;
use App\Enums\InsyteAiTool;
use App\Enums\PlanCapability;
use App\Enums\PlanLimitKey;
use App\Models\AiConversation;
use App\Models\User;
use App\Support\Platform\TenantPlanAccess;
use Illuminate\Support\Str;

class RunInsyteAiTurn
{
    public function __construct(
        private AiChatClient $aiChatClient,
        private ExecuteInsyteAiTool $executeInsyteAiTool,
        private AssertPlanLimit $assertPlanLimit,
        private TenantPlanAccess $planAccess,
    ) {}

    /**
     * @return array{
     *     conversation_id: int,
     *     reply: string,
     *     status: string,
     *     actions: list<array{ok: bool, mutated: bool, message: string, lead_url: ?string}>
     * }
     */
    public function handle(User $user, string $message, ?AiConversation $conversation = null): array
    {
        $this->assertPlanLimit->handle(PlanLimitKey::AiMessagesMonthly);

        $conversation ??= AiConversation::query()->create([
            'user_id' => $user->id,
            'title' => Str::limit($message, 72),
            'status' => AiConversationStatus::Open,
            'last_message_at' => now(),
        ]);

        $conversation->messages()->create([
            'role' => 'user',
            'content' => $message,
        ]);

        $history = $this->history($conversation, $user);
        $executed = [];
        $reply = '';

        for ($round = 0; $round < 5; $round++) {
            $completion = $this->aiChatClient->complete($history, $this->toolDefinitions());

            if (! $completion->hasToolCalls()) {
                $reply = $completion->text !== ''
                    ? $completion->text
                    : __('I could not complete that. Try a more specific prompt.');
                break;
            }

            $toolLines = [];

            foreach ($completion->toolCalls as $call) {
                $result = $this->executeInsyteAiTool->handle($call->name, $call->arguments, $user);
                $executed[] = $result;
                $toolLines[] = $call->name.': '.$result['message'];
            }

            $history[] = [
                'role' => 'assistant',
                'content' => $completion->text !== '' ? $completion->text : __('Running CRM actions.'),
            ];
            $history[] = [
                'role' => 'user',
                'content' => __('Tool results:')."\n".implode("\n", $toolLines)."\n".__('Reply to the user based on these results. Do not mention tools.'),
            ];
        }

        if ($reply === '') {
            $reply = collect($executed)->pluck('message')->filter()->implode("\n")
                ?: __('Done.');
        }

        $mutated = collect($executed)->contains(fn (array $result): bool => $result['mutated'] === true);

        if ($mutated) {
            $conversation->status = AiConversationStatus::Completed;
        }

        $conversation->last_message_at = now();
        $conversation->save();

        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $reply,
            'actions' => $executed === [] ? null : $executed,
        ]);

        return [
            'conversation_id' => $conversation->id,
            'reply' => $reply,
            'status' => $conversation->status->value,
            'actions' => $executed,
        ];
    }

    /**
     * @return list<array{role: string, content: string}>
     */
    private function history(AiConversation $conversation, User $user): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->instructions($user),
            ],
        ];

        foreach ($conversation->messages()->orderBy('id')->get() as $message) {
            $role = $message->role === 'assistant' ? 'assistant' : 'user';
            $messages[] = [
                'role' => $role,
                'content' => $message->content,
            ];
        }

        return $messages;
    }

    private function instructions(User $user): string
    {
        return implode("\n", [
            'You are InSyte AI OS, a sales copilot inside InSyte CRM.',
            'You act as '.$user->name.'. Only use the provided tools. Never invent lead ids.',
            'Search for a lead before scheduling, completing, or creating work when the user gives a name.',
            'If several leads match, list them and ask which one.',
            'Timezone: '.config('app.timezone').'. Today is '.now()->toDateString().'.',
            'Keep replies short. After a successful action, say what changed.',
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function toolDefinitions(): array
    {
        return array_values(array_filter(
            InsyteAiTool::definitions(),
            function (array $definition): bool {
                $tool = InsyteAiTool::tryFrom($definition['name'] ?? '');

                if ($tool === null) {
                    return false;
                }

                return $this->planAccess->hasCapability(PlanCapability::fromInsyteAiTool($tool));
            },
        ));
    }
}
