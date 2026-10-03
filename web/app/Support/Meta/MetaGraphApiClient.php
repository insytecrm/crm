<?php

namespace App\Support\Meta;

use App\Contracts\MetaGraphClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MetaGraphApiClient implements MetaGraphClient
{
    private const GRAPH_VERSION = 'v21.0';

    public function exchangeCodeForUserToken(string $code, string $redirectUri): string
    {
        $payload = $this->get('oauth/access_token', null, [
            'client_id' => $this->appId(),
            'client_secret' => $this->appSecret(),
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        $token = $payload['access_token'] ?? null;

        if (! is_string($token) || $token === '') {
            throw new RuntimeException(__('Facebook Login did not return an access token.'));
        }

        return $token;
    }

    public function exchangeForLongLivedUserToken(string $shortLivedUserToken): string
    {
        $payload = $this->get('oauth/access_token', null, [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->appId(),
            'client_secret' => $this->appSecret(),
            'fb_exchange_token' => $shortLivedUserToken,
        ]);

        $token = $payload['access_token'] ?? null;

        if (! is_string($token) || $token === '') {
            return $shortLivedUserToken;
        }

        return $token;
    }

    public function userPages(string $userAccessToken): array
    {
        $payload = $this->get('me/accounts', $userAccessToken, [
            'fields' => 'id,name,access_token',
            'limit' => 100,
        ]);

        return collect($payload['data'] ?? [])
            ->map(static fn (array $page): array => [
                'id' => (string) ($page['id'] ?? ''),
                'name' => (string) ($page['name'] ?? 'Facebook Page'),
                'access_token' => (string) ($page['access_token'] ?? ''),
            ])
            ->filter(static fn (array $page): bool => $page['id'] !== '' && $page['access_token'] !== '')
            ->values()
            ->all();
    }

    public function page(string $pageId, string $accessToken): array
    {
        $payload = $this->get($pageId, $accessToken, [
            'fields' => 'id,name',
        ]);

        return [
            'id' => (string) ($payload['id'] ?? $pageId),
            'name' => (string) ($payload['name'] ?? 'Facebook Page'),
        ];
    }

    public function campaigns(string $pageId, string $accessToken): array
    {
        try {
            $payload = $this->get("{$pageId}/campaigns", $accessToken, [
                'fields' => 'id,name,status',
                'limit' => 50,
            ]);
        } catch (RuntimeException) {
            return [];
        }

        return collect($payload['data'] ?? [])
            ->map(static fn (array $campaign): array => [
                'id' => (string) ($campaign['id'] ?? ''),
                'name' => (string) ($campaign['name'] ?? __('Untitled campaign')),
                'status' => isset($campaign['status']) ? (string) $campaign['status'] : null,
            ])
            ->filter(static fn (array $campaign): bool => $campaign['id'] !== '')
            ->values()
            ->all();
    }

    public function leadForms(string $pageId, string $accessToken): array
    {
        $payload = $this->get("{$pageId}/leadgen_forms", $accessToken, [
            'fields' => 'id,name,status,questions',
            'limit' => 100,
        ]);

        return collect($payload['data'] ?? [])
            ->map(function (array $form): array {
                $questions = collect($form['questions'] ?? [])
                    ->map(static function (array $question): array {
                        $key = (string) ($question['key'] ?? $question['name'] ?? '');
                        $label = (string) ($question['label'] ?? $key);

                        return [
                            'key' => $key,
                            'label' => $label !== '' ? $label : $key,
                        ];
                    })
                    ->filter(static fn (array $question): bool => $question['key'] !== '')
                    ->values()
                    ->all();

                return [
                    'id' => (string) ($form['id'] ?? ''),
                    'name' => (string) ($form['name'] ?? __('Untitled form')),
                    'status' => isset($form['status']) ? (string) $form['status'] : null,
                    'questions' => $questions,
                ];
            })
            ->filter(static fn (array $form): bool => $form['id'] !== '')
            ->values()
            ->all();
    }

    public function lead(string $leadgenId, string $accessToken): array
    {
        $payload = $this->get($leadgenId, $accessToken, [
            'fields' => 'id,form_id,field_data',
        ]);

        $fieldData = [];

        foreach ($payload['field_data'] ?? [] as $field) {
            if (! is_array($field)) {
                continue;
            }

            $name = (string) ($field['name'] ?? '');
            $values = $field['values'] ?? [];
            $value = is_array($values) ? (string) ($values[0] ?? '') : (string) $values;

            if ($name !== '') {
                $fieldData[$name] = $value;
            }
        }

        return [
            'id' => (string) ($payload['id'] ?? $leadgenId),
            'form_id' => (string) ($payload['form_id'] ?? ''),
            'field_data' => $fieldData,
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function get(string $path, ?string $accessToken, array $query = []): array
    {
        if (is_string($accessToken) && $accessToken !== '') {
            $query['access_token'] = $accessToken;
        }

        try {
            $response = Http::connectTimeout(5)
                ->timeout(20)
                ->acceptJson()
                ->get($this->url($path), $query)
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            $message = data_get($exception->response?->json(), 'error.message');

            throw new RuntimeException(
                is_string($message) && $message !== ''
                    ? $message
                    : __('Facebook Graph API request failed.'),
                previous: $exception,
            );
        }

        if (! is_array($response)) {
            throw new RuntimeException(__('Facebook Graph API returned an unexpected response.'));
        }

        return $response;
    }

    private function url(string $path): string
    {
        return 'https://graph.facebook.com/'.self::GRAPH_VERSION.'/'.ltrim($path, '/');
    }

    private function appId(): string
    {
        $appId = config('services.meta.app_id');

        if (! is_string($appId) || $appId === '') {
            throw new RuntimeException(__('META_APP_ID is not configured.'));
        }

        return $appId;
    }

    private function appSecret(): string
    {
        $appSecret = config('services.meta.app_secret');

        if (! is_string($appSecret) || $appSecret === '') {
            throw new RuntimeException(__('META_APP_SECRET is not configured.'));
        }

        return $appSecret;
    }
}
