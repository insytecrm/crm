<?php

namespace App\Http\Controllers\Landing;

use App\Enums\LandingSubmissionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLandingDemoRequest;
use App\Http\Requests\StoreLandingTrialRequest;
use App\Models\LandingSubmission;
use Illuminate\Http\JsonResponse;

class LandingSubmissionController extends Controller
{
    public function storeDemo(StoreLandingDemoRequest $request): JsonResponse
    {
        LandingSubmission::query()->create([
            ...$request->validated(),
            'type' => LandingSubmissionType::Demo,
        ]);

        return response()->json([
            'message' => 'Thank you! Our team will call you within 24 hours.',
        ]);
    }

    public function storeTrial(StoreLandingTrialRequest $request): JsonResponse
    {
        LandingSubmission::query()->create([
            ...$request->validated(),
            'type' => LandingSubmissionType::Trial,
        ]);

        return response()->json([
            'message' => "You're in! Check your email to activate your account.",
        ]);
    }
}
