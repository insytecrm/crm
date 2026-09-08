<?php

use App\Enums\PropertyPortal;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\MetaLeadWebhookController;
use App\Http\Controllers\Api\PortalWebhookController;
use App\Http\Middleware\AuthenticateLeadApiToken;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware([
    'throttle:60,1',
    AuthenticateLeadApiToken::class,
])->group(function () {
    Route::post('leads', [LeadController::class, 'store'])->name('api.v1.leads.store');
});

Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('webhooks/{portal}/{webhookId}', [PortalWebhookController::class, 'receive'])
        ->whereIn('portal', array_column(PropertyPortal::cases(), 'value'))
        ->where('webhookId', 'wh_[A-Za-z0-9]+')
        ->name('api.webhooks.portal.receive');

    Route::get('webhooks/meta/leads', [MetaLeadWebhookController::class, 'verify'])
        ->name('api.webhooks.meta.leads.verify');
    Route::post('webhooks/meta/leads', [MetaLeadWebhookController::class, 'receive'])
        ->name('api.webhooks.meta.leads.receive');
});
