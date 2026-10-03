<?php

namespace App\Actions;

use App\Models\FacebookPageConnection;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;
use Throwable;

class StartFacebookOAuth
{
    public const SCOPES = [
        'pages_show_list',
        'pages_read_engagement',
        'pages_manage_metadata',
        'leads_retrieval',
        'ads_read',
    ];

    public function handle(FacebookPageConnection $connection): string
    {
        $appId = config('services.meta.app_id');
        $redirectUri = $this->redirectUri();

        if (! is_string($appId) || $appId === '') {
            throw new RuntimeException(__('Facebook Login is not configured. Set META_APP_ID and META_APP_SECRET, then try again.'));
        }

        $tenantId = tenant('id');

        if (! is_string($tenantId) || $tenantId === '') {
            throw new RuntimeException(__('Facebook Login requires an active workspace.'));
        }

        $state = $this->encodeState([
            'tenant_id' => $tenantId,
            'connection_id' => $connection->id,
            'page_id' => $connection->page_id,
        ]);

        return 'https://www.facebook.com/v21.0/dialog/oauth?'.http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => implode(',', self::SCOPES),
            'response_type' => 'code',
        ]);
    }

    public function redirectUri(): string
    {
        $configured = config('services.meta.oauth_redirect_uri');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return route('api.oauth.facebook.callback', absolute: true);
    }

    /**
     * @param  array{tenant_id: string, connection_id: int, page_id: string}  $payload
     */
    public function encodeState(array $payload): string
    {
        return Crypt::encryptString(json_encode([
            ...$payload,
            'exp' => now()->addMinutes(15)->getTimestamp(),
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * @return array{tenant_id: string, connection_id: int, page_id: string}
     */
    public function decodeState(string $state): array
    {
        try {
            /** @var array{tenant_id?: mixed, connection_id?: mixed, page_id?: mixed, exp?: mixed} $payload */
            $payload = json_decode(Crypt::decryptString($state), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            throw new RuntimeException(__('Facebook Login expired. Click Verify again.'));
        }

        $expiresAt = (int) ($payload['exp'] ?? 0);

        if ($expiresAt < now()->getTimestamp()) {
            throw new RuntimeException(__('Facebook Login expired. Click Verify again.'));
        }

        $tenantId = (string) ($payload['tenant_id'] ?? '');
        $connectionId = (int) ($payload['connection_id'] ?? 0);
        $pageId = (string) ($payload['page_id'] ?? '');

        if ($tenantId === '' || $connectionId < 1 || $pageId === '') {
            throw new RuntimeException(__('Facebook Login state is invalid. Click Verify again.'));
        }

        return [
            'tenant_id' => $tenantId,
            'connection_id' => $connectionId,
            'page_id' => $pageId,
        ];
    }
}
