<?php

namespace App\Http\Controllers\Api;

use App\Actions\CompleteFacebookOAuthCallback;
use App\Actions\StartFacebookOAuth;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class FacebookOAuthController extends Controller
{
    /**
     * Meta Facebook Login OAuth redirect target.
     */
    public function callback(
        Request $request,
        CompleteFacebookOAuthCallback $completeFacebookOAuthCallback,
        StartFacebookOAuth $startFacebookOAuth,
    ): RedirectResponse {
        if ($request->filled('error')) {
            Log::warning('facebook.oauth.callback.denied', [
                'error' => $request->query('error'),
                'error_reason' => $request->query('error_reason'),
                'error_description' => $request->query('error_description'),
            ]);

            return $this->redirectForState($request->query('state'), $startFacebookOAuth)
                ->withErrors(['verify' => __('Facebook Login was cancelled or denied.')]);
        }

        $code = $request->query('code');
        $state = $request->query('state');

        if (! is_string($code) || $code === '' || ! is_string($state) || $state === '') {
            return $this->fallbackRedirect()
                ->withErrors(['verify' => __('Facebook Login response was incomplete. Click Verify again.')]);
        }

        try {
            $result = $completeFacebookOAuthCallback->handle($code, $state);
        } catch (RuntimeException $exception) {
            return $this->redirectForFlashedTenant()
                ->withErrors(['verify' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            Log::error('facebook.oauth.callback.failed', [
                'message' => $exception->getMessage(),
            ]);

            return $this->redirectForFlashedTenant()
                ->withErrors(['verify' => __('Facebook Login failed. Please try Verify again.')]);
        }

        return redirect()
            ->route('tenant.settings.integrations.facebook.show', ['tenant' => $result['tenant_id']])
            ->with('status', __('Facebook account connected and Page verified. Select Lead Forms, map fields, then activate.'));
    }

    private function redirectForState(mixed $state, StartFacebookOAuth $startFacebookOAuth): RedirectResponse
    {
        if (! is_string($state) || $state === '') {
            return $this->fallbackRedirect();
        }

        try {
            $payload = $startFacebookOAuth->decodeState($state);
        } catch (RuntimeException) {
            return $this->fallbackRedirect();
        }

        return redirect()->route('tenant.settings.integrations.facebook.show', [
            'tenant' => $payload['tenant_id'],
        ]);
    }

    private function redirectForFlashedTenant(): RedirectResponse
    {
        $tenantId = session()->pull('facebook_oauth_return_tenant');

        if (is_string($tenantId) && $tenantId !== '') {
            return redirect()->route('tenant.settings.integrations.facebook.show', ['tenant' => $tenantId]);
        }

        return $this->fallbackRedirect();
    }

    private function fallbackRedirect(): RedirectResponse
    {
        $tenantId = tenant('id');

        if (is_string($tenantId) && $tenantId !== '') {
            return redirect()->route('tenant.settings.integrations.facebook.show', ['tenant' => $tenantId]);
        }

        return redirect('/');
    }
}
