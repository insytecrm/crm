<?php

namespace App\Actions;

use Illuminate\Http\Request;

class VerifyMetaWebhook
{
    /**
     * Validate Meta's webhook subscription handshake and return the challenge, or null on failure.
     */
    public function handle(Request $request): ?string
    {
        $mode = $request->query('hub_mode', $request->query('hub.mode'));
        $token = $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = $request->query('hub_challenge', $request->query('hub.challenge'));

        $expected = config('services.meta.verify_token');

        if (! is_string($expected) || $expected === '') {
            return null;
        }

        if ($mode !== 'subscribe') {
            return null;
        }

        if (! is_string($token) || ! hash_equals($expected, $token)) {
            return null;
        }

        if (! is_string($challenge) || $challenge === '') {
            return null;
        }

        return $challenge;
    }
}
