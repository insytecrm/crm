<?php

namespace App\Actions;

use App\Contracts\MetaGraphClient;
use App\Enums\FacebookLeadMappableField;
use App\Enums\FacebookPageConnectionStatus;
use App\Enums\LeadBudget;
use App\Enums\LeadSource;
use App\Enums\PropertyType;
use App\Enums\TenantStatus;
use App\Models\FacebookPageConnection;
use App\Models\FacebookPageRegistration;
use App\Support\LeadSourcePath;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class ProcessMetaLeadWebhook
{
    public function __construct(
        private MetaGraphClient $metaGraphClient,
        private CreateLead $createLead,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array{created: int, skipped: int}
     */
    public function handle(array $payload): array
    {
        $created = 0;
        $skipped = 0;

        foreach ($payload['entry'] ?? [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            foreach ($entry['changes'] ?? [] as $change) {
                if (! is_array($change) || ($change['field'] ?? null) !== 'leadgen') {
                    continue;
                }

                $value = $change['value'] ?? null;

                if (! is_array($value)) {
                    $skipped++;

                    continue;
                }

                $result = $this->ingestLeadgen($value);
                $created += $result['created'];
                $skipped += $result['skipped'];
            }
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * @param  array<string, mixed>  $value
     * @return array{created: int, skipped: int}
     */
    private function ingestLeadgen(array $value): array
    {
        $pageId = (string) ($value['page_id'] ?? '');
        $formId = (string) ($value['form_id'] ?? '');
        $leadgenId = (string) ($value['leadgen_id'] ?? '');

        if ($pageId === '' || $leadgenId === '') {
            return ['created' => 0, 'skipped' => 1];
        }

        $registration = FacebookPageRegistration::query()
            ->where('page_id', $pageId)
            ->where('is_active', true)
            ->first();

        if ($registration === null) {
            Log::info('meta.leads.webhook.unregistered_page', ['page_id' => $pageId]);

            return ['created' => 0, 'skipped' => 1];
        }

        $tenant = $registration->tenant;

        if ($tenant === null || $tenant->status === TenantStatus::Suspended) {
            return ['created' => 0, 'skipped' => 1];
        }

        tenancy()->initialize($tenant);

        try {
            $connection = FacebookPageConnection::query()
                ->where('page_id', $pageId)
                ->where('status', FacebookPageConnectionStatus::Connected)
                ->first();

            if ($connection === null) {
                return ['created' => 0, 'skipped' => 1];
            }

            if ($formId !== '' && ! $connection->acceptsForm($formId)) {
                $connection->forceFill([
                    'total_skipped' => $connection->total_skipped + 1,
                ])->save();

                return ['created' => 0, 'skipped' => 1];
            }

            $accessToken = $connection->resolvedAccessToken();

            if ($accessToken === null) {
                $connection->forceFill([
                    'last_error' => __('Facebook access token is missing.'),
                    'total_failed' => $connection->total_failed + 1,
                ])->save();

                return ['created' => 0, 'skipped' => 1];
            }

            $leadPayload = $this->metaGraphClient->lead($leadgenId, $accessToken);
            $mapped = $this->mapFields($leadPayload['field_data'], $connection, $value, $formId);

            $validated = Validator::make($mapped, [
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30'],
                'email' => ['nullable', 'email', 'max:255'],
                'source' => ['required', Rule::enum(LeadSource::class)],
                'sub_source' => ['nullable', 'string', 'max:500'],
                'source_context' => ['nullable', 'array'],
                'budget' => ['nullable', Rule::enum(LeadBudget::class)],
                'location' => ['nullable', 'string', 'max:255'],
                'property_type' => ['nullable', Rule::enum(PropertyType::class)],
                'configuration' => ['nullable', 'string', 'max:255'],
            ]);

            if ($validated->fails()) {
                $connection->forceFill([
                    'total_skipped' => $connection->total_skipped + 1,
                    'last_lead_at' => now(),
                ])->save();

                return ['created' => 0, 'skipped' => 1];
            }

            $this->createLead->handle($validated->validated(), null);

            $connection->forceFill([
                'total_synced' => $connection->total_synced + 1,
                'last_lead_at' => now(),
                'last_error' => null,
            ])->save();

            return ['created' => 1, 'skipped' => 0];
        } catch (Throwable $exception) {
            $connection = FacebookPageConnection::query()->where('page_id', $pageId)->first();

            if ($connection !== null) {
                $connection->forceFill([
                    'last_error' => $exception->getMessage(),
                    'total_failed' => $connection->total_failed + 1,
                ])->save();
            }

            Log::warning('meta.leads.webhook.ingest_failed', [
                'page_id' => $pageId,
                'leadgen_id' => $leadgenId,
                'message' => $exception->getMessage(),
            ]);

            return ['created' => 0, 'skipped' => 1];
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }
        }
    }

    /**
     * @param  array<string, string>  $fieldData
     * @param  array<string, mixed>  $webhookValue
     * @return array<string, mixed>
     */
    private function mapFields(
        array $fieldData,
        FacebookPageConnection $connection,
        array $webhookValue,
        string $formId,
    ): array {
        $map = $connection->field_map ?? [];
        $payload = [
            'source' => LeadSource::Facebook->value,
            ...$this->resolveSourcePath($connection, $webhookValue, $formId),
        ];

        foreach (FacebookLeadMappableField::cases() as $field) {
            $formKey = $map[$field->value] ?? null;

            if (! is_string($formKey) || $formKey === '') {
                continue;
            }

            $value = $fieldData[$formKey] ?? null;

            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            if ($field === FacebookLeadMappableField::Source) {
                continue;
            }

            $payload[$field->value] = trim($value);
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $webhookValue
     * @return array{sub_source: string|null, source_context: array{segments: list<array{key: string, id: string|null, label: string}>}|null}
     */
    private function resolveSourcePath(
        FacebookPageConnection $connection,
        array $webhookValue,
        string $formId,
    ): array {
        $segments = [];

        if (filled($connection->page_name) || filled($connection->page_id)) {
            $segments[] = [
                'key' => 'page',
                'id' => $connection->page_id,
                'label' => $connection->page_name ?: $connection->page_id,
            ];
        }

        $campaignId = (string) ($webhookValue['campaign_id'] ?? '');

        if ($campaignId !== '') {
            $segments[] = [
                'key' => 'campaign',
                'id' => $campaignId,
                'label' => $this->resolveCampaignName($connection, $campaignId) ?: $campaignId,
            ];
        }

        if ($formId !== '') {
            $segments[] = [
                'key' => 'form',
                'id' => $formId,
                'label' => $this->resolveFormName($connection, $formId) ?: $formId,
            ];
        }

        return LeadSourcePath::fromSegments($segments);
    }

    private function resolveCampaignName(FacebookPageConnection $connection, string $campaignId): ?string
    {
        foreach ($connection->campaigns ?? [] as $campaign) {
            if (! is_array($campaign)) {
                continue;
            }

            if ((string) ($campaign['id'] ?? '') === $campaignId) {
                $name = trim((string) ($campaign['name'] ?? ''));

                return $name !== '' ? $name : null;
            }
        }

        return null;
    }

    private function resolveFormName(FacebookPageConnection $connection, string $formId): ?string
    {
        foreach ($connection->lead_forms ?? [] as $form) {
            if (! is_array($form)) {
                continue;
            }

            if ((string) ($form['id'] ?? '') === $formId) {
                $name = trim((string) ($form['name'] ?? ''));

                return $name !== '' ? $name : null;
            }
        }

        return null;
    }
}
