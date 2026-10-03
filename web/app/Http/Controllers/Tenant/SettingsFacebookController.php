<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\ActivateFacebookPageConnection;
use App\Actions\CreateFacebookPageConnection;
use App\Actions\RemoveFacebookPageConnection;
use App\Actions\StartFacebookOAuth;
use App\Actions\VerifyFacebookPageConnection;
use App\Enums\FacebookLeadMappableField;
use App\Enums\FacebookPageConnectionStatus;
use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ActivateFacebookPageConnectionRequest;
use App\Http\Requests\Tenant\StoreFacebookPageConnectionRequest;
use App\Models\FacebookPageConnection;
use App\Models\FacebookPageRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use InvalidArgumentException;
use RuntimeException;

class SettingsFacebookController extends Controller
{
    public function show(): View
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsView), 403);

        $connection = FacebookPageConnection::query()->first();

        return view('tenant.settings.integrations.facebook.show', [
            'connection' => $connection,
            'mappableFields' => FacebookLeadMappableField::cases(),
            'canManage' => auth()->user()->hasPermission(TenantPermission::IntegrationsManage),
        ]);
    }

    public function store(
        StoreFacebookPageConnectionRequest $request,
        CreateFacebookPageConnection $createFacebookPageConnection,
    ): RedirectResponse {
        try {
            $connection = $createFacebookPageConnection->handle(
                $request->connectionData(),
                $request->user(),
            );
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['page_id' => $exception->getMessage()]);
        }

        return redirect()
            ->route('tenant.settings.integrations.facebook.show')
            ->with('status', __('Facebook Page saved. Verify access to continue.'));
    }

    public function verify(
        FacebookPageConnection $facebookPage,
        VerifyFacebookPageConnection $verifyFacebookPageConnection,
        StartFacebookOAuth $startFacebookOAuth,
    ): RedirectResponse {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsManage), 403);

        $hasPageToken = is_string($facebookPage->page_access_token) && $facebookPage->page_access_token !== '';

        if (! $hasPageToken) {
            try {
                return redirect()->away($startFacebookOAuth->handle($facebookPage));
            } catch (RuntimeException $exception) {
                return redirect()
                    ->route('tenant.settings.integrations.facebook.show')
                    ->withErrors(['verify' => $exception->getMessage()]);
            }
        }

        try {
            $verifyFacebookPageConnection->handle($facebookPage);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('tenant.settings.integrations.facebook.show')
                ->withErrors(['verify' => $exception->getMessage()]);
        }

        return redirect()
            ->route('tenant.settings.integrations.facebook.show')
            ->with('status', __('Page verified. Select Lead Forms, map fields, then activate.'));
    }

    public function activate(
        ActivateFacebookPageConnectionRequest $request,
        FacebookPageConnection $facebookPage,
        ActivateFacebookPageConnection $activateFacebookPageConnection,
    ): RedirectResponse {
        try {
            $activateFacebookPageConnection->handle(
                $facebookPage,
                $request->formIds(),
                $request->fieldMap(),
            );
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['activate' => $exception->getMessage()]);
        }

        return redirect()
            ->route('tenant.settings.integrations.facebook.show')
            ->with('status', __('Facebook Lead Ads activated. New leads from selected forms will appear in your CRM.'));
    }

    public function pause(FacebookPageConnection $facebookPage): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsManage), 403);

        if (! $facebookPage->isConnected()) {
            return back()->withErrors([
                'activate' => __('Only an active Facebook connection can be paused.'),
            ]);
        }

        $facebookPage->forceFill([
            'status' => FacebookPageConnectionStatus::Paused,
        ])->save();

        FacebookPageRegistration::query()
            ->where('page_id', $facebookPage->page_id)
            ->update(['is_active' => false]);

        return back()->with('status', __('Facebook Lead Ads paused. New leads will not be imported until resumed.'));
    }

    public function resume(FacebookPageConnection $facebookPage): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsManage), 403);

        if (! $facebookPage->isPaused() || empty($facebookPage->field_map) || empty($facebookPage->selected_form_ids)) {
            return back()->withErrors([
                'activate' => __('This Facebook connection cannot be resumed yet.'),
            ]);
        }

        $facebookPage->forceFill([
            'status' => FacebookPageConnectionStatus::Connected,
            'connected_at' => $facebookPage->connected_at ?? now(),
        ])->save();

        FacebookPageRegistration::query()->updateOrCreate(
            ['page_id' => $facebookPage->page_id],
            [
                'tenant_id' => (string) tenant('id'),
                'is_active' => true,
            ],
        );

        return back()->with('status', __('Facebook Lead Ads resumed.'));
    }

    public function destroy(
        FacebookPageConnection $facebookPage,
        RemoveFacebookPageConnection $removeFacebookPageConnection,
    ): RedirectResponse {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsManage), 403);

        $removeFacebookPageConnection->handle($facebookPage);

        return redirect()
            ->route('tenant.settings.integrations.facebook.show')
            ->with('status', __('Facebook Page disconnected.'));
    }
}
