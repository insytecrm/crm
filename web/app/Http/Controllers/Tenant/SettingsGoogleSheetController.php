<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\ConnectGoogleSheetConnection;
use App\Actions\CreateGoogleSheetConnection;
use App\Actions\SyncGoogleSheetLeads;
use App\Actions\VerifyGoogleSheetConnection;
use App\Enums\GoogleSheetConnectionStatus;
use App\Enums\GoogleSheetMappableField;
use App\Enums\TenantPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ConnectGoogleSheetConnectionRequest;
use App\Http\Requests\Tenant\StoreGoogleSheetConnectionRequest;
use App\Models\GoogleSheetConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class SettingsGoogleSheetController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsView), 403);

        $connections = GoogleSheetConnection::query()
            ->with('createdBy')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('tenant.settings.integrations.google-sheets.index', [
            'connections' => $connections,
            'summary' => [
                'total_sheets' => $connections->count(),
                'total_synced' => (int) $connections->sum('total_synced'),
                'total_skipped' => (int) $connections->sum('total_skipped'),
                'total_failed' => (int) $connections->sum('total_failed'),
            ],
            'canManage' => auth()->user()->hasPermission(TenantPermission::IntegrationsManage),
            'openAddModal' => session('open_add_google_sheet_modal', false)
                || old('name') !== null
                || old('spreadsheet_url') !== null,
        ]);
    }

    public function store(
        StoreGoogleSheetConnectionRequest $request,
        CreateGoogleSheetConnection $createGoogleSheetConnection,
    ): RedirectResponse {
        try {
            $connection = $createGoogleSheetConnection->handle(
                $request->connectionData(),
                $request->user(),
            );
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->with('open_add_google_sheet_modal', true)
                ->withErrors(['spreadsheet_url' => $exception->getMessage()]);
        }

        return redirect()
            ->route('tenant.settings.integrations.google-sheets.show', $connection)
            ->with('status', __('Google Sheet saved. Verify access to continue.'));
    }

    public function show(GoogleSheetConnection $googleSheet): View
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsView), 403);

        return view('tenant.settings.integrations.google-sheets.show', [
            'connection' => $googleSheet,
            'mappableFields' => GoogleSheetMappableField::cases(),
            'canManage' => auth()->user()->hasPermission(TenantPermission::IntegrationsManage),
        ]);
    }

    public function verify(
        GoogleSheetConnection $googleSheet,
        VerifyGoogleSheetConnection $verifyGoogleSheetConnection,
    ): RedirectResponse {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsManage), 403);

        try {
            $verifyGoogleSheetConnection->handle($googleSheet);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('tenant.settings.integrations.google-sheets.show', $googleSheet)
                ->withErrors(['verify' => $exception->getMessage()]);
        }

        return redirect()
            ->route('tenant.settings.integrations.google-sheets.show', $googleSheet)
            ->with('status', __('Sheet verified. Map columns to connect.'));
    }

    public function connect(
        ConnectGoogleSheetConnectionRequest $request,
        GoogleSheetConnection $googleSheet,
        ConnectGoogleSheetConnection $connectGoogleSheetConnection,
        SyncGoogleSheetLeads $syncGoogleSheetLeads,
    ): RedirectResponse {
        try {
            $connection = $connectGoogleSheetConnection->handle(
                $googleSheet,
                $request->columnMap(),
            );
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['column_map' => $exception->getMessage()]);
        }

        try {
            $result = $syncGoogleSheetLeads->handle($connection);
        } catch (Throwable) {
            return redirect()
                ->route('tenant.settings.integrations.google-sheets.show', $connection)
                ->with('status', __('Sheet connected. Initial sync failed — new rows will retry on the next sync.'));
        }

        return redirect()
            ->route('tenant.settings.integrations.google-sheets.show', $connection)
            ->with('status', __('Sheet connected. :count lead(s) imported.', [
                'count' => $result['created'],
            ]));
    }

    public function sync(
        GoogleSheetConnection $googleSheet,
        SyncGoogleSheetLeads $syncGoogleSheetLeads,
    ): RedirectResponse {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsManage), 403);

        if (! $googleSheet->isConnected()) {
            return back()->withErrors([
                'sync' => __('Only active sheets can be synced.'),
            ]);
        }

        try {
            $result = $syncGoogleSheetLeads->handle($googleSheet);
        } catch (Throwable) {
            return back()->withErrors([
                'sync' => $googleSheet->fresh()?->last_error ?? __('Google Sheets sync failed.'),
            ]);
        }

        return back()->with('status', __('Synced :count lead(s) from :name.', [
            'count' => $result['created'],
            'name' => $googleSheet->name,
        ]));
    }

    public function syncAll(SyncGoogleSheetLeads $syncGoogleSheetLeads): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsManage), 403);

        $created = 0;
        $failed = 0;

        GoogleSheetConnection::query()
            ->connected()
            ->orderBy('id')
            ->each(function (GoogleSheetConnection $connection) use ($syncGoogleSheetLeads, &$created, &$failed): void {
                try {
                    $result = $syncGoogleSheetLeads->handle($connection);
                    $created += $result['created'];
                } catch (Throwable) {
                    $failed++;
                }
            });

        return back()->with('status', __('Synced all active sheets. :count lead(s) imported (:failed failed).', [
            'count' => $created,
            'failed' => $failed,
        ]));
    }

    public function pause(GoogleSheetConnection $googleSheet): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsManage), 403);

        if (! $googleSheet->isConnected()) {
            return back()->withErrors([
                'sync' => __('Only active sheets can be paused.'),
            ]);
        }

        $googleSheet->forceFill([
            'status' => GoogleSheetConnectionStatus::Paused,
        ])->save();

        return back()->with('status', __('Sheet paused. New rows will not sync until resumed.'));
    }

    public function resume(GoogleSheetConnection $googleSheet): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsManage), 403);

        if (! $googleSheet->isPaused() || empty($googleSheet->column_map)) {
            return back()->withErrors([
                'sync' => __('This sheet cannot be resumed yet.'),
            ]);
        }

        $googleSheet->forceFill([
            'status' => GoogleSheetConnectionStatus::Connected,
            'connected_at' => $googleSheet->connected_at ?? now(),
        ])->save();

        return back()->with('status', __('Sheet resumed and is syncing again.'));
    }

    public function destroy(GoogleSheetConnection $googleSheet): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsManage), 403);

        $googleSheet->delete();

        return redirect()
            ->route('tenant.settings.integrations.google-sheets.index')
            ->with('status', __('Google Sheet disconnected.'));
    }
}
