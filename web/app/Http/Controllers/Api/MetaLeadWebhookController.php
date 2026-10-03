<?php

namespace App\Http\Controllers\Api;

use App\Actions\ProcessMetaLeadWebhook;
use App\Actions\VerifyMetaWebhook;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class MetaLeadWebhookController extends Controller
{
    /**
     * Meta webhook verification handshake (Lead Ads / Page subscriptions).
     */
    public function verify(Request $request, VerifyMetaWebhook $verifyMetaWebhook): Response
    {
        $challenge = $verifyMetaWebhook->handle($request);

        if ($challenge === null) {
            return response('Forbidden', SymfonyResponse::HTTP_FORBIDDEN);
        }

        return response($challenge, SymfonyResponse::HTTP_OK)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Receive Meta leadgen / page webhook events and create CRM leads when a Page is activated.
     */
    public function receive(Request $request, ProcessMetaLeadWebhook $processMetaLeadWebhook): Response
    {
        if (! $this->signatureIsValid($request)) {
            return response('Invalid signature', SymfonyResponse::HTTP_FORBIDDEN);
        }

        $result = $processMetaLeadWebhook->handle($request->all());

        Log::info('meta.leads.webhook.received', [
            'object' => $request->input('object'),
            'entry_count' => count($request->input('entry', [])),
            'created' => $result['created'],
            'skipped' => $result['skipped'],
        ]);

        return response('EVENT_RECEIVED', SymfonyResponse::HTTP_OK)
            ->header('Content-Type', 'text/plain');
    }

    private function signatureIsValid(Request $request): bool
    {
        $appSecret = config('services.meta.app_secret');

        // Allow local verification/testing before the app secret is configured.
        if (! is_string($appSecret) || $appSecret === '') {
            return true;
        }

        $header = $request->header('X-Hub-Signature-256');

        if (! is_string($header) || ! str_starts_with($header, 'sha256=')) {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $appSecret);

        return hash_equals($expected, $header);
    }
}
