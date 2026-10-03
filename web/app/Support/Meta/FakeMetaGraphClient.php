<?php

namespace App\Support\Meta;

use App\Contracts\MetaGraphClient;
use RuntimeException;

class FakeMetaGraphClient implements MetaGraphClient
{
    /**
     * @param  array<string, array{name?: string, campaigns?: list<array{id: string, name: string, status: string|null}>, forms?: list<array{id: string, name: string, status: string|null, questions: list<array{key: string, label: string}>}>, access_token?: string, error?: string}>  $pages
     * @param  array<string, array{id: string, form_id: string, field_data: array<string, string>}|array{error: string}>  $leads
     * @param  array<string, string>  $codes
     */
    public function __construct(
        private array $pages = [],
        private array $leads = [],
        private array $codes = [],
    ) {}

    /**
     * @param  list<array{id: string, name: string, status?: string|null}>  $campaigns
     * @param  list<array{id: string, name: string, status?: string|null, questions?: list<array{key: string, label: string}>}>  $forms
     */
    public function seedPage(
        string $pageId,
        string $name = 'Demo Facebook Page',
        array $campaigns = [],
        array $forms = [],
        string $accessToken = 'fake-page-token',
    ): self {
        $this->pages[$pageId] = [
            'name' => $name,
            'access_token' => $accessToken,
            'campaigns' => array_map(
                static fn (array $campaign): array => [
                    'id' => $campaign['id'],
                    'name' => $campaign['name'],
                    'status' => $campaign['status'] ?? 'ACTIVE',
                ],
                $campaigns,
            ),
            'forms' => array_map(
                static fn (array $form): array => [
                    'id' => $form['id'],
                    'name' => $form['name'],
                    'status' => $form['status'] ?? 'ACTIVE',
                    'questions' => $form['questions'] ?? [
                        ['key' => 'full_name', 'label' => 'Full Name'],
                        ['key' => 'phone_number', 'label' => 'Phone Number'],
                        ['key' => 'email', 'label' => 'Email'],
                    ],
                ],
                $forms,
            ),
        ];

        return $this;
    }

    public function seedOAuthCode(string $code, string $userToken = 'fake-user-token'): self
    {
        $this->codes[$code] = $userToken;

        return $this;
    }

    public function failPage(string $pageId, string $message = 'Could not access this Facebook Page.'): self
    {
        $this->pages[$pageId] = ['error' => $message];

        return $this;
    }

    /**
     * @param  array<string, string>  $fieldData
     */
    public function seedLead(string $leadgenId, string $formId, array $fieldData): self
    {
        $this->leads[$leadgenId] = [
            'id' => $leadgenId,
            'form_id' => $formId,
            'field_data' => $fieldData,
        ];

        return $this;
    }

    public function exchangeCodeForUserToken(string $code, string $redirectUri): string
    {
        if (! array_key_exists($code, $this->codes)) {
            throw new RuntimeException(__('Facebook Login code is invalid or expired.'));
        }

        return $this->codes[$code];
    }

    public function exchangeForLongLivedUserToken(string $shortLivedUserToken): string
    {
        return 'long-'.$shortLivedUserToken;
    }

    public function userPages(string $userAccessToken): array
    {
        return collect($this->pages)
            ->filter(static fn (array $page): bool => ! isset($page['error']))
            ->map(fn (array $page, string $pageId): array => [
                'id' => $pageId,
                'name' => (string) ($page['name'] ?? 'Facebook Page'),
                'access_token' => (string) ($page['access_token'] ?? 'fake-page-token'),
            ])
            ->values()
            ->all();
    }

    public function page(string $pageId, string $accessToken): array
    {
        $page = $this->requirePage($pageId);

        return [
            'id' => $pageId,
            'name' => (string) ($page['name'] ?? 'Facebook Page'),
        ];
    }

    public function campaigns(string $pageId, string $accessToken): array
    {
        $page = $this->requirePage($pageId);

        return array_values($page['campaigns'] ?? []);
    }

    public function leadForms(string $pageId, string $accessToken): array
    {
        $page = $this->requirePage($pageId);

        return array_values($page['forms'] ?? []);
    }

    public function lead(string $leadgenId, string $accessToken): array
    {
        if (! array_key_exists($leadgenId, $this->leads)) {
            throw new RuntimeException(__('Facebook lead could not be retrieved.'));
        }

        $lead = $this->leads[$leadgenId];

        if (isset($lead['error'])) {
            throw new RuntimeException((string) $lead['error']);
        }

        return [
            'id' => (string) $lead['id'],
            'form_id' => (string) $lead['form_id'],
            'field_data' => $lead['field_data'] ?? [],
        ];
    }

    /**
     * @return array{name?: string, access_token?: string, campaigns?: list<array{id: string, name: string, status: string|null}>, forms?: list<array{id: string, name: string, status: string|null, questions: list<array{key: string, label: string}>}>, error?: string}
     */
    private function requirePage(string $pageId): array
    {
        if (! array_key_exists($pageId, $this->pages)) {
            throw new RuntimeException(__('Facebook Page not found. Check the Page ID and try again.'));
        }

        $page = $this->pages[$pageId];

        if (isset($page['error'])) {
            throw new RuntimeException((string) $page['error']);
        }

        return $page;
    }
}
