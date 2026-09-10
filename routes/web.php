<?php

use App\Http\Controllers\Api\FacebookOAuthController;
use App\Http\Controllers\Landing\LandingSubmissionController;
use App\Http\Controllers\Platform\BillingAdjustmentController;
use App\Http\Controllers\Platform\BillingInvoiceController;
use App\Http\Controllers\Platform\BillingPaymentController;
use App\Http\Controllers\Platform\BillingSubscriptionController;
use App\Http\Controllers\Platform\ChannelPartnerController;
use App\Http\Controllers\Platform\ChannelPartnerUserController;
use App\Http\Controllers\Platform\ChannelPartnerWizardController;
use App\Http\Controllers\Platform\DashboardController;
use App\Http\Controllers\Platform\PlanController;
use App\Http\Controllers\Platform\PlanWizardController;
use App\Http\Controllers\Platform\PlatformLeadController;
use App\Http\Controllers\Platform\QuotationController;
use App\Http\Controllers\Platform\QuotationWizardController;
use App\Http\Controllers\Platform\RevenueOverviewController;
use App\Http\Controllers\Platform\StubPageController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Site\SitePageController;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', SitePageController::class)->defaults('page', 'home')->name('site.home');

        Route::get('/crm', SitePageController::class)->defaults('page', 'crm')->name('site.crm');
        Route::get('/automation', SitePageController::class)->defaults('page', 'automation')->name('site.automation');
        Route::get('/ai', SitePageController::class)->defaults('page', 'ai')->name('site.ai');
        Route::get('/integrations', SitePageController::class)->defaults('page', 'integrations')->name('site.integrations');
        Route::get('/customization', SitePageController::class)->defaults('page', 'customization')->name('site.customization');

        Route::get('/solutions/real-estate', SitePageController::class)->defaults('page', 'solutions-real-estate')->name('site.solutions.real-estate');
        Route::get('/solutions/sales-teams', SitePageController::class)->defaults('page', 'solutions-sales-teams')->name('site.solutions.sales-teams');
        Route::get('/solutions/small-businesses', SitePageController::class)->defaults('page', 'solutions-small-businesses')->name('site.solutions.small-businesses');
        Route::get('/solutions/agencies', SitePageController::class)->defaults('page', 'solutions-agencies')->name('site.solutions.agencies');
        Route::get('/solutions/other-industries', SitePageController::class)->defaults('page', 'solutions-other')->name('site.solutions.other');

        Route::get('/pricing', SitePageController::class)->defaults('page', 'pricing')->name('site.pricing');
        Route::get('/demo', SitePageController::class)->defaults('page', 'demo')->name('site.demo');
        Route::get('/signup', SitePageController::class)->defaults('page', 'signup')->name('site.signup');

        Route::get('/faqs', SitePageController::class)->defaults('page', 'faqs')->name('site.faqs');
        Route::get('/blog', SitePageController::class)->defaults('page', 'blog')->name('site.blog');
        Route::get('/about', SitePageController::class)->defaults('page', 'about')->name('site.about');
        Route::get('/contact', SitePageController::class)->defaults('page', 'contact')->name('site.contact');
        Route::get('/careers', SitePageController::class)->defaults('page', 'careers')->name('site.careers');

        Route::get('/help', SitePageController::class)->defaults('page', 'help')->name('site.help');
        Route::get('/help/{slug}', SitePageController::class)->defaults('page', 'help')->name('site.help.show');
        Route::get('/docs', SitePageController::class)->defaults('page', 'docs')->name('site.docs');
        Route::get('/docs/{slug}', SitePageController::class)->defaults('page', 'docs')->name('site.docs.show');
        Route::get('/guides', SitePageController::class)->defaults('page', 'guides')->name('site.guides');
        Route::get('/guides/{slug}', SitePageController::class)->defaults('page', 'guides')->name('site.guides.show');
    });
}

Route::prefix('landing')->name('landing.')->group(function () {
    Route::post('/demo', [LandingSubmissionController::class, 'storeDemo'])->name('demo.store');
    Route::post('/trial', [LandingSubmissionController::class, 'storeTrial'])->name('trial.store');
});

Route::view('/privacy', 'landing.privacy')->name('privacy');
Route::view('/terms', 'landing.terms')->name('terms');
Route::view('/refund', 'landing.refund')->name('refund');
Route::view('/cookies', 'landing.cookies')->name('cookies');
Route::view('/security', 'landing.security')->name('security');

Route::prefix('platform')->group(function () {
    require __DIR__.'/auth.php';

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    Route::middleware(['auth', 'superadmin'])->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('platform.dashboard');

        Route::get('/leads', [PlatformLeadController::class, 'index'])->name('platform.leads');
        Route::get('/leads/create', [PlatformLeadController::class, 'create'])->name('platform.leads.create');
        Route::post('/leads', [PlatformLeadController::class, 'store'])->name('platform.leads.store');
        Route::get('/leads/{lead}', [PlatformLeadController::class, 'show'])->name('platform.leads.show');
        Route::patch('/leads/{lead}', [PlatformLeadController::class, 'update'])->name('platform.leads.update');
        Route::patch('/leads/{lead}/stage', [PlatformLeadController::class, 'updateStage'])->name('platform.leads.stage.update');
        Route::post('/leads/{lead}/notes', [PlatformLeadController::class, 'storeNote'])->name('platform.leads.notes.store');
        Route::patch('/leads/{lead}/next-action', [PlatformLeadController::class, 'updateNextAction'])->name('platform.leads.next-action.update');

        Route::get('/plans', [PlanController::class, 'index'])->name('platform.plans');
        Route::get('/plans/create', [PlanWizardController::class, 'create'])->name('platform.plans.create');
        Route::get('/plans/wizard/basic', [PlanWizardController::class, 'basic'])->name('platform.plans.wizard.basic');
        Route::post('/plans/wizard/basic', [PlanWizardController::class, 'storeBasic'])->name('platform.plans.wizard.basic.store');
        Route::get('/plans/wizard/pricing', [PlanWizardController::class, 'pricing'])->name('platform.plans.wizard.pricing');
        Route::post('/plans/wizard/pricing', [PlanWizardController::class, 'storePricing'])->name('platform.plans.wizard.pricing.store');
        Route::get('/plans/wizard/features', [PlanWizardController::class, 'features'])->name('platform.plans.wizard.features');
        Route::post('/plans/wizard/features', [PlanWizardController::class, 'storeFeatures'])->name('platform.plans.wizard.features.store');
        Route::get('/plans/wizard/limits', [PlanWizardController::class, 'limits'])->name('platform.plans.wizard.limits');
        Route::post('/plans/wizard/limits', [PlanWizardController::class, 'storeLimits'])->name('platform.plans.wizard.limits.store');
        Route::get('/plans/wizard/review', [PlanWizardController::class, 'review'])->name('platform.plans.wizard.review');
        Route::post('/plans', [PlanWizardController::class, 'store'])->name('platform.plans.store');
        Route::post('/plans/presets', [PlanController::class, 'updatePresets'])->name('platform.plans.presets.update');
        Route::get('/plans/{plan}', [PlanController::class, 'show'])->name('platform.plans.show');
        Route::get('/plans/{plan}/features', [PlanController::class, 'features'])->name('platform.plans.features');
        Route::get('/plans/{plan}/limits', [PlanController::class, 'limits'])->name('platform.plans.limits');
        Route::get('/plans/{plan}/partners', [PlanController::class, 'partners'])->name('platform.plans.partners');
        Route::get('/plans/{plan}/edit', [PlanController::class, 'edit'])->name('platform.plans.edit');
        Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('platform.plans.update');
        Route::post('/plans/{plan}/archive', [PlanController::class, 'archive'])->name('platform.plans.archive');
        Route::get('/plans/{plan}/duplicate', [PlanController::class, 'duplicate'])->name('platform.plans.duplicate');
        Route::post('/plans/{plan}/duplicate', [PlanController::class, 'storeDuplicate'])->name('platform.plans.duplicate.store');

        Route::get('/revenue', RevenueOverviewController::class)->name('platform.revenue');
        Route::get('/revenue/export', [RevenueOverviewController::class, 'export'])->name('platform.revenue.export');

        Route::get('/revenue/subscriptions', [BillingSubscriptionController::class, 'index'])->name('platform.revenue.subscriptions');
        Route::get('/revenue/subscriptions/{subscription}', [BillingSubscriptionController::class, 'show'])->name('platform.revenue.subscriptions.show');
        Route::post('/revenue/subscriptions/{subscription}/change-plan', [BillingSubscriptionController::class, 'changePlan'])->name('platform.revenue.subscriptions.change-plan');
        Route::post('/revenue/subscriptions/{subscription}/extend-trial', [BillingSubscriptionController::class, 'extendTrial'])->name('platform.revenue.subscriptions.extend-trial');
        Route::post('/revenue/subscriptions/{subscription}/pause', [BillingSubscriptionController::class, 'pause'])->name('platform.revenue.subscriptions.pause');
        Route::post('/revenue/subscriptions/{subscription}/resume', [BillingSubscriptionController::class, 'resume'])->name('platform.revenue.subscriptions.resume');
        Route::post('/revenue/subscriptions/{subscription}/cancel', [BillingSubscriptionController::class, 'cancel'])->name('platform.revenue.subscriptions.cancel');
        Route::post('/revenue/subscriptions/{subscription}/discount', [BillingSubscriptionController::class, 'applyDiscount'])->name('platform.revenue.subscriptions.discount');

        Route::get('/revenue/invoices', [BillingInvoiceController::class, 'index'])->name('platform.revenue.invoices');
        Route::get('/revenue/invoices/{invoice}', [BillingInvoiceController::class, 'show'])->name('platform.revenue.invoices.show');
        Route::get('/revenue/invoices/{invoice}/download', [BillingInvoiceController::class, 'download'])->name('platform.revenue.invoices.download');
        Route::post('/revenue/invoices/{invoice}/send', [BillingInvoiceController::class, 'send'])->name('platform.revenue.invoices.send');
        Route::post('/revenue/invoices/{invoice}/mark-paid', [BillingInvoiceController::class, 'markPaid'])->name('platform.revenue.invoices.mark-paid');

        Route::get('/revenue/payments', [BillingPaymentController::class, 'index'])->name('platform.revenue.payments');
        Route::get('/revenue/payments/{payment}', [BillingPaymentController::class, 'show'])->name('platform.revenue.payments.show');
        Route::post('/revenue/payments/{payment}/retry', [BillingPaymentController::class, 'retry'])->name('platform.revenue.payments.retry');
        Route::post('/revenue/payments/{payment}/remind', [BillingPaymentController::class, 'remind'])->name('platform.revenue.payments.remind');

        Route::get('/revenue/adjustments', [BillingAdjustmentController::class, 'index'])->name('platform.revenue.adjustments');
        Route::get('/revenue/adjustments/discounts', [BillingAdjustmentController::class, 'discounts'])->name('platform.revenue.adjustments.discounts');
        Route::get('/revenue/adjustments/refunds', [BillingAdjustmentController::class, 'refunds'])->name('platform.revenue.adjustments.refunds');

        Route::get('/quotations', [QuotationController::class, 'index'])->name('platform.quotations');
        Route::get('/quotations/create', [QuotationWizardController::class, 'create'])->name('platform.quotations.create');
        Route::get('/quotations/wizard/prospect', [QuotationWizardController::class, 'prospect'])->name('platform.quotations.wizard.prospect');
        Route::post('/quotations/wizard/prospect', [QuotationWizardController::class, 'storeProspect'])->name('platform.quotations.wizard.prospect.store');
        Route::get('/quotations/wizard/plan', [QuotationWizardController::class, 'plan'])->name('platform.quotations.wizard.plan');
        Route::post('/quotations/wizard/plan', [QuotationWizardController::class, 'storePlan'])->name('platform.quotations.wizard.plan.store');
        Route::get('/quotations/wizard/pricing', [QuotationWizardController::class, 'pricing'])->name('platform.quotations.wizard.pricing');
        Route::post('/quotations/wizard/pricing', [QuotationWizardController::class, 'storePricing'])->name('platform.quotations.wizard.pricing.store');
        Route::get('/quotations/wizard/review', [QuotationWizardController::class, 'review'])->name('platform.quotations.wizard.review');
        Route::post('/quotations', [QuotationWizardController::class, 'store'])->name('platform.quotations.store');
        Route::post('/quotations/modal', [QuotationWizardController::class, 'storeModal'])->name('platform.quotations.modal.store');
        Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('platform.quotations.show');
        Route::get('/quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('platform.quotations.edit');
        Route::put('/quotations/{quotation}', [QuotationController::class, 'update'])->name('platform.quotations.update');
        Route::post('/quotations/{quotation}/send', [QuotationController::class, 'send'])->name('platform.quotations.send');
        Route::post('/quotations/{quotation}/accept', [QuotationController::class, 'accept'])->name('platform.quotations.accept');
        Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('platform.quotations.reject');
        Route::post('/quotations/{quotation}/duplicate', [QuotationController::class, 'duplicate'])->name('platform.quotations.duplicate');
        Route::get('/quotations/{quotation}/download', [QuotationController::class, 'download'])->name('platform.quotations.download');
        Route::get('/quotations/{quotation}/onboard', [QuotationController::class, 'onboardForm'])->name('platform.quotations.onboard');
        Route::post('/quotations/{quotation}/onboard', [QuotationController::class, 'onboard'])->name('platform.quotations.onboard.store');
        Route::post('/quotations/{quotation}/create-subscription', [QuotationController::class, 'createSubscription'])->name('platform.quotations.create-subscription');

        Route::get('/integrations', StubPageController::class)->name('platform.integrations');
        Route::get('/analytics', StubPageController::class)->name('platform.analytics');
        Route::get('/utilities', StubPageController::class)->name('platform.utilities');
        Route::get('/settings', StubPageController::class)->name('platform.settings');

        Route::get('tenants/create', [ChannelPartnerWizardController::class, 'company'])->name('tenants.create');
        Route::post('tenants/wizard', [ChannelPartnerWizardController::class, 'store'])->name('tenants.wizard.store');
        Route::get('tenants/wizard/{tenant}/success', [ChannelPartnerWizardController::class, 'success'])->name('tenants.wizard.success');

        Route::get('tenants/{tenant}/users', [ChannelPartnerController::class, 'users'])->name('tenants.users');
        Route::put('tenants/{tenant}/users/{user}', [ChannelPartnerUserController::class, 'update'])->name('tenants.users.update');
        Route::patch('tenants/{tenant}/users/{user}/status', [ChannelPartnerUserController::class, 'updateStatus'])->name('tenants.users.status');
        Route::post('tenants/{tenant}/users/{user}/reset-access', [ChannelPartnerUserController::class, 'resetAccess'])->name('tenants.users.reset-access');
        Route::get('tenants/{tenant}/subscription', [ChannelPartnerController::class, 'subscription'])->name('tenants.subscription');
        Route::get('tenants/{tenant}/usage', [ChannelPartnerController::class, 'usage'])->name('tenants.usage');
        Route::get('tenants/{tenant}/integrations', [ChannelPartnerController::class, 'integrations'])->name('tenants.integrations');
        Route::get('tenants/{tenant}/activity', [ChannelPartnerController::class, 'activity'])->name('tenants.activity');

        Route::resource('tenants', TenantController::class)->except(['create', 'show']);
        Route::get('tenants/{tenant}', [ChannelPartnerController::class, 'overview'])->name('tenants.show');
    });
});

// Facebook Login callback (needs web/session middleware; keep /api path for Meta redirect URI)
Route::get('api/oauth/facebook/callback', [FacebookOAuthController::class, 'callback'])
    ->middleware('throttle:60,1')
    ->name('api.oauth.facebook.callback');
