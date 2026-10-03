<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\RunInsyteAiTurn;
use App\Enums\AiOsTab;
use App\Enums\PlanCapability;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreInsyteAiChatRequest;
use App\Models\AiConversation;
use App\Models\User;
use App\Queries\InsyteAiSuggestions;
use App\Support\Ai\InsyteAiPageContent;
use App\Support\Platform\TenantPlanAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InsyteAiController extends Controller
{
    public function index(Request $request, InsyteAiSuggestions $suggestions, TenantPlanAccess $planAccess): View
    {
        /** @var User $user */
        $user = $request->user();

        $conversation = null;

        if ($request->filled('conversation')) {
            $conversation = AiConversation::query()
                ->ownedBy($user)
                ->with('messages')
                ->find($request->integer('conversation'));
        }

        return view('tenant.ai.index', [
            'tabs' => array_values(array_filter(
                AiOsTab::cases(),
                function (AiOsTab $tab) use ($planAccess): bool {
                    $capability = PlanCapability::fromAiOsTab($tab);

                    return $capability === null || $planAccess->hasCapability($capability);
                },
            )),
            'examples' => InsyteAiPageContent::examples(),
            'quickActions' => InsyteAiPageContent::quickActions(),
            'tip' => InsyteAiPageContent::tip(),
            'suggestions' => $suggestions->forUser($user),
            'conversations' => AiConversation::query()
                ->ownedBy($user)
                ->latest('last_message_at')
                ->latest('id')
                ->limit(12)
                ->get(),
            'conversation' => $conversation,
            'starterPrompt' => $request->string('prompt')->toString(),
            'chatUrl' => route('tenant.ai.chat'),
        ]);
    }

    public function chat(StoreInsyteAiChatRequest $request, RunInsyteAiTurn $runInsyteAiTurn): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = null;

        if ($request->filled('conversation_id')) {
            $conversation = AiConversation::query()
                ->ownedBy($user)
                ->find($request->integer('conversation_id'));

            abort_unless($conversation !== null, 404);
        }

        $result = $runInsyteAiTurn->handle(
            $user,
            $request->validated('message'),
            $conversation,
        );

        return response()->json($result);
    }
}
