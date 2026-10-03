<?php

namespace App\Actions;

use App\Contracts\MetaGraphClient;
use App\Models\FacebookPageConnection;
use App\Models\Tenant;
use RuntimeException;

class CompleteFacebookOAuthCallback
{
    public function __construct(
        private MetaGraphClient $metaGraphClient,
        private VerifyFacebookPageConnection $verifyFacebookPageConnection,
        private StartFacebookOAuth $startFacebookOAuth,
    ) {}

    /**
     * @return array{tenant_id: string, connection_id: int}
     */
    public function handle(string $code, string $state): array
    {
        $payload = $this->startFacebookOAuth->decodeState($state);
        $tenantId = $payload['tenant_id'];
        $connectionId = $payload['connection_id'];
        $expectedPageId = $payload['page_id'];

        $tenant = Tenant::query()->find($tenantId);

        if ($tenant === null) {
            throw new RuntimeException(__('Workspace for this Facebook Login was not found.'));
        }

        try {
            $shortLived = $this->metaGraphClient->exchangeCodeForUserToken(
                $code,
                $this->startFacebookOAuth->redirectUri(),
            );
            $userToken = $this->metaGraphClient->exchangeForLongLivedUserToken($shortLived);
            $pages = $this->metaGraphClient->userPages($userToken);

            $matched = collect($pages)->first(
                static fn (array $page): bool => (string) ($page['id'] ?? '') === $expectedPageId,
            );

            if ($matched === null) {
                throw new RuntimeException(__('Your Facebook account does not have access to Page ID :page. Ask your agency for Page access, then try again.', [
                    'page' => $expectedPageId,
                ]));
            }

            $pageToken = (string) ($matched['access_token'] ?? '');

            if ($pageToken === '') {
                throw new RuntimeException(__('Facebook did not return a Page access token for this Page.'));
            }

            tenancy()->initialize($tenant);

            $connection = FacebookPageConnection::query()->find($connectionId);

            if ($connection === null || $connection->page_id !== $expectedPageId) {
                throw new RuntimeException(__('Facebook Page connection was not found. Save the Page ID and try again.'));
            }

            $connection->forceFill([
                'page_access_token' => $pageToken,
                'last_error' => null,
            ])->save();

            $this->verifyFacebookPageConnection->handle($connection->fresh());
        } catch (RuntimeException $exception) {
            session()->flash('facebook_oauth_return_tenant', $tenantId);

            throw $exception;
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }
        }

        return [
            'tenant_id' => $tenantId,
            'connection_id' => $connectionId,
        ];
    }
}
