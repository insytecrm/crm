<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateLead;
use App\Actions\NormalizePortalLeadPayload;
use App\Enums\LeadBudget;
use App\Enums\LeadSource;
use App\Enums\PropertyPortal;
use App\Enums\PropertyType;
use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LeadResource;
use App\Models\PortalWebhookEndpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PortalWebhookController extends Controller
{
    public function receive(
        Request $request,
        string $portal,
        string $webhookId,
        NormalizePortalLeadPayload $normalizePortalLeadPayload,
        CreateLead $createLead,
    ): JsonResponse {
        $portalEnum = PropertyPortal::tryFrom($portal);

        if ($portalEnum === null) {
            abort(404);
        }

        $endpoint = PortalWebhookEndpoint::query()
            ->where('webhook_id', $webhookId)
            ->where('portal', $portalEnum->value)
            ->first();

        if ($endpoint === null) {
            abort(404);
        }

        if (! $endpoint->is_active) {
            abort(403, __('This webhook is inactive.'));
        }

        $secret = $this->extractSecret($request);

        if ($secret === null || ! hash_equals($endpoint->secret_hash, hash('sha256', $secret))) {
            abort(401, __('Invalid webhook secret.'));
        }

        $tenant = $endpoint->tenant;

        if ($tenant === null || $tenant->status === TenantStatus::Suspended) {
            abort(403, __('This company account is suspended.'));
        }

        tenancy()->initialize($tenant);

        $endpoint->forceFill([
            'last_used_at' => now(),
        ])->save();

        $normalized = $normalizePortalLeadPayload->handle($request->all(), $portalEnum);

        $validated = Validator::make($normalized, [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'source' => ['required', Rule::enum(LeadSource::class)],
            'budget' => ['nullable', Rule::enum(LeadBudget::class)],
            'location' => ['nullable', 'string', 'max:255'],
            'property_type' => ['nullable', Rule::enum(PropertyType::class)],
            'configuration' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $lead = $createLead->handle($validated, null);

        return (new LeadResource($lead))
            ->response()
            ->setStatusCode(201);
    }

    private function extractSecret(Request $request): ?string
    {
        $bearer = $request->bearerToken();

        if (is_string($bearer) && $bearer !== '') {
            return $bearer;
        }

        $header = $request->header('X-Webhook-Secret');

        if (is_string($header) && $header !== '') {
            return $header;
        }

        return null;
    }
}
