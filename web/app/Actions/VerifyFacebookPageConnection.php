<?php

namespace App\Actions;

use App\Contracts\MetaGraphClient;
use App\Enums\FacebookPageConnectionStatus;
use App\Models\FacebookPageConnection;
use RuntimeException;

class VerifyFacebookPageConnection
{
    public function __construct(private MetaGraphClient $metaGraphClient) {}

    public function handle(FacebookPageConnection $connection): FacebookPageConnection
    {
        $accessToken = $connection->resolvedAccessToken();

        if ($accessToken === null) {
            $message = __('Facebook access is not configured. Ask your agency for Page access, then try again.');

            $connection->forceFill([
                'last_error' => $message,
                'status' => FacebookPageConnectionStatus::Draft,
                'verified_at' => null,
                'connected_at' => null,
                'campaigns' => null,
                'lead_forms' => null,
                'form_fields' => null,
                'field_map' => null,
                'selected_form_ids' => null,
            ])->save();

            throw new RuntimeException($message);
        }

        try {
            $page = $this->metaGraphClient->page($connection->page_id, $accessToken);
            $campaigns = $this->metaGraphClient->campaigns($connection->page_id, $accessToken);
            $forms = $this->metaGraphClient->leadForms($connection->page_id, $accessToken);

            $formFields = collect($forms)
                ->flatMap(static fn (array $form) => $form['questions'] ?? [])
                ->unique('key')
                ->values()
                ->all();

            $connection->forceFill([
                'page_name' => $page['name'],
                'page_access_token' => $connection->page_access_token ?: $accessToken,
                'campaigns' => $campaigns,
                'lead_forms' => $forms,
                'form_fields' => $formFields,
                'status' => FacebookPageConnectionStatus::Verified,
                'verified_at' => now(),
                'last_error' => null,
                'field_map' => null,
                'selected_form_ids' => null,
                'connected_at' => null,
            ])->save();
        } catch (RuntimeException $exception) {
            $connection->forceFill([
                'last_error' => $exception->getMessage(),
                'status' => FacebookPageConnectionStatus::Draft,
                'verified_at' => null,
                'connected_at' => null,
                'campaigns' => null,
                'lead_forms' => null,
                'form_fields' => null,
                'field_map' => null,
                'selected_form_ids' => null,
            ])->save();

            throw $exception;
        }

        return $connection->fresh();
    }
}
