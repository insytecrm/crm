<?php

namespace App\Contracts;

interface MetaGraphClient
{
    /**
     * Exchange a Facebook Login authorization code for a short-lived user access token.
     */
    public function exchangeCodeForUserToken(string $code, string $redirectUri): string;

    /**
     * Exchange a short-lived user token for a long-lived user token.
     */
    public function exchangeForLongLivedUserToken(string $shortLivedUserToken): string;

    /**
     * @return list<array{id: string, name: string, access_token: string}>
     */
    public function userPages(string $userAccessToken): array;

    /**
     * @return array{id: string, name: string}
     */
    public function page(string $pageId, string $accessToken): array;

    /**
     * @return list<array{id: string, name: string, status: string|null}>
     */
    public function campaigns(string $pageId, string $accessToken): array;

    /**
     * @return list<array{id: string, name: string, status: string|null, questions: list<array{key: string, label: string}>}>
     */
    public function leadForms(string $pageId, string $accessToken): array;

    /**
     * @return array{id: string, form_id: string, field_data: array<string, string>}
     */
    public function lead(string $leadgenId, string $accessToken): array;
}
