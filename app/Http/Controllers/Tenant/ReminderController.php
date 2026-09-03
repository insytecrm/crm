<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\DismissReminderRequest;
use App\Queries\DueReminders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function due(Request $request, DueReminders $dueReminders): JsonResponse
    {
        $user = $request->user();

        abort_unless($user !== null, 401);

        return response()->json([
            'reminders' => $dueReminders->forUser($user)->values(),
        ]);
    }

    public function dismiss(DismissReminderRequest $request, DueReminders $dueReminders): JsonResponse
    {
        $user = $request->user();

        abort_unless($user !== null, 401);

        $dismissed = $dueReminders->dismiss(
            $user,
            $request->validated('subject_type'),
            (int) $request->validated('subject_id'),
        );

        abort_unless($dismissed, 404);

        return response()->json(['dismissed' => true]);
    }
}
