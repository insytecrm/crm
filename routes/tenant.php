<?php

declare(strict_types=1);

use App\Enums\PropertyPortal;
use App\Http\Controllers\Tenant\ActivityController;
use App\Http\Controllers\Tenant\ActivityDueNotificationController;
use App\Http\Controllers\Tenant\AuthenticatedSessionController;
use App\Http\Controllers\Tenant\AutomationController;
use App\Http\Controllers\Tenant\AutomationWorkflowController;
use App\Http\Controllers\Tenant\BookingController;
use App\Http\Controllers\Tenant\BulkTableDeleteController;
use App\Http\Controllers\Tenant\CloseLeadController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\DashboardPipelineController;
use App\Http\Controllers\Tenant\DuplicateLeadController;
use App\Http\Controllers\Tenant\FollowUpController;
use App\Http\Controllers\Tenant\InsyteAiController;
use App\Http\Controllers\Tenant\IntegrationController;
use App\Http\Controllers\Tenant\InvoiceController;
use App\Http\Controllers\Tenant\LeadActivityController;
use App\Http\Controllers\Tenant\LeadBudgetController;
use App\Http\Controllers\Tenant\LeadBulkActionController;
use App\Http\Controllers\Tenant\LeadController;
use App\Http\Controllers\Tenant\LeadDocumentController;
use App\Http\Controllers\Tenant\LeadExportController;
use App\Http\Controllers\Tenant\LeadFollowUpController;
use App\Http\Controllers\Tenant\LeadImportController;
use App\Http\Controllers\Tenant\LeadNoteController;
use App\Http\Controllers\Tenant\LeadPropertyTypeController;
use App\Http\Controllers\Tenant\LeadRoutingRuleController;
use App\Http\Controllers\Tenant\LeadScheduledEventController;
use App\Http\Controllers\Tenant\LeadSiteVisitController;
use App\Http\Controllers\Tenant\LeadStatusController;
use App\Http\Controllers\Tenant\LeadTablePreferencesController;
use App\Http\Controllers\Tenant\LeadTaskController;
use App\Http\Controllers\Tenant\LeadWhatsAppController;
use App\Http\Controllers\Tenant\MarkLeadLostController;
use App\Http\Controllers\Tenant\MessageTemplateController;
use App\Http\Controllers\Tenant\PayoutController;
use App\Http\Controllers\Tenant\PropertyController;
use App\Http\Controllers\Tenant\PropertyExportController;
use App\Http\Controllers\Tenant\PropertyImportController;
use App\Http\Controllers\Tenant\PropertyImportSampleController;
use App\Http\Controllers\Tenant\PropertyMicrositeCmsController;
use App\Http\Controllers\Tenant\PropertyMicrositeController;
use App\Http\Controllers\Tenant\ReportAnalyticsController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\ReportExportController;
use App\Http\Controllers\Tenant\ReportPrintController;
use App\Http\Controllers\Tenant\RevenueController;
use App\Http\Controllers\Tenant\SalesTeamController;
use App\Http\Controllers\Tenant\SalesTeamMemberController;
use App\Http\Controllers\Tenant\SettingsController;
use App\Http\Controllers\Tenant\SettingsDomainController;
use App\Http\Controllers\Tenant\SettingsFacebookController;
use App\Http\Controllers\Tenant\SettingsGoogleSheetController;
use App\Http\Controllers\Tenant\SettingsLeadApiController;
use App\Http\Controllers\Tenant\SettingsPortalWebhookController;
use App\Http\Controllers\Tenant\SettingsRoleController;
use App\Http\Controllers\Tenant\SettingsUserController;
use App\Http\Controllers\Tenant\SiteVisitController;
use App\Http\Controllers\Tenant\TablePreferencesController;
use App\Http\Controllers\Tenant\TaskController;
use App\Http\Controllers\Tenant\TeamChatController;
use App\Http\Controllers\Tenant\TeamPerformanceController;
use App\Http\Controllers\Tenant\WhatsAppWebController;
use App\Http\Middleware\EnforceTenantPlanAccess;
use App\Http\Middleware\EnsureVerifiedDomainPurpose;
use App\Http\Middleware\PreventAccessByPausedSubscription;
use App\Http\Middleware\PreventAccessBySuspendedTenant;
use App\Http\Middleware\SetTenantUrlDefaults;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByPath::class,
    SetTenantUrlDefaults::class,
    PreventAccessBySuspendedTenant::class,
    PreventAccessByPausedSubscription::class,
])->prefix('{tenant}')->where(['tenant' => '^(?!platform$).*$'])->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('tenant.login');

        Route::post('login', [AuthenticatedSessionController::class, 'store']);
    });

    Route::get('projects/{slug}', [PropertyMicrositeController::class, 'show'])
        ->name('tenant.projects.microsite.show');
    Route::get('projects/{slug}/media/{key}', [PropertyMicrositeController::class, 'media'])
        ->where('key', '[A-Za-z0-9\-]+')
        ->name('tenant.projects.microsite.media');
    Route::post('projects/{slug}/enquire', [PropertyMicrositeController::class, 'enquire'])
        ->middleware('throttle:8,1')
        ->name('tenant.projects.microsite.enquire');

    Route::middleware(['auth', EnforceTenantPlanAccess::class])->group(function () {
        Route::get('dashboard', DashboardController::class)->name('tenant.dashboard');
        Route::get('dashboard/pipeline', DashboardPipelineController::class)->name('tenant.dashboard.pipeline');
        Route::get('ai', [InsyteAiController::class, 'index'])
            ->middleware('permission:ai.use')
            ->name('tenant.ai.index');
        Route::post('ai/chat', [InsyteAiController::class, 'chat'])
            ->middleware(['permission:ai.use', 'throttle:20,1'])
            ->name('tenant.ai.chat');

        Route::get('leads/duplicates', [DuplicateLeadController::class, 'index'])->name('tenant.leads.duplicates.index');
        Route::post('leads/duplicates/merge', [DuplicateLeadController::class, 'merge'])->middleware('permission:leads.update')->name('tenant.leads.duplicates.merge');
        Route::get('leads', [LeadController::class, 'index'])->name('tenant.leads.index');
        Route::get('leads/priority', [LeadController::class, 'priority'])->name('tenant.leads.priority.index');
        Route::get('leads/unassigned', [LeadController::class, 'unassigned'])->name('tenant.leads.unassigned.index');
        Route::get('leads/converted', [LeadController::class, 'converted'])->name('tenant.leads.converted.index');
        Route::get('leads/lost', [LeadController::class, 'lost'])->name('tenant.leads.lost.index');
        Route::patch('leads/table-preferences', [LeadTablePreferencesController::class, 'update'])->name('tenant.leads.table-preferences.update');
        Route::post('leads', [LeadController::class, 'store'])->name('tenant.leads.store');
        Route::delete('leads/bulk', [LeadController::class, 'bulkDestroy'])->name('tenant.leads.bulk-destroy');
        Route::patch('leads/bulk/assign', [LeadBulkActionController::class, 'assign'])->name('tenant.leads.bulk-assign');
        Route::patch('leads/bulk/status', [LeadBulkActionController::class, 'updateStatus'])->name('tenant.leads.bulk-status.update');
        Route::get('leads-export', LeadExportController::class)->name('tenant.leads.export');
        Route::post('leads-import', LeadImportController::class)->name('tenant.leads.import');

        Route::get('leads/{lead}', [LeadController::class, 'show'])->name('tenant.leads.show');
        Route::patch('leads/{lead}', [LeadController::class, 'update'])->name('tenant.leads.update');
        Route::patch('leads/{lead}/status', [LeadStatusController::class, 'update'])->name('tenant.leads.status.update');
        Route::patch('leads/{lead}/budget', [LeadBudgetController::class, 'update'])->name('tenant.leads.budget.update');
        Route::patch('leads/{lead}/property-type', [LeadPropertyTypeController::class, 'update'])->name('tenant.leads.property-type.update');
        Route::post('leads/{lead}/close', [CloseLeadController::class, 'store'])->name('tenant.leads.close');
        Route::post('leads/{lead}/mark-lost', [MarkLeadLostController::class, 'store'])->name('tenant.leads.mark-lost');
        Route::post('leads/{lead}/activities', [LeadActivityController::class, 'store'])->name('tenant.leads.activities.store');
        Route::get('leads/{lead}/whatsapp', [LeadWhatsAppController::class, 'show'])->name('tenant.leads.whatsapp.show');
        Route::post('leads/{lead}/whatsapp', [LeadWhatsAppController::class, 'store'])->name('tenant.leads.whatsapp.store');
        Route::post('leads/{lead}/follow-up', [LeadFollowUpController::class, 'store'])->name('tenant.leads.follow-up.store');
        Route::post('leads/{lead}/follow-up/complete', [LeadFollowUpController::class, 'complete'])->name('tenant.leads.follow-up.complete');
        Route::post('leads/{lead}/site-visit', [LeadSiteVisitController::class, 'store'])->name('tenant.leads.site-visit.store');
        Route::post('leads/{lead}/site-visit/complete', [LeadSiteVisitController::class, 'complete'])->name('tenant.leads.site-visit.complete');

        Route::post('leads/{lead}/tasks', [LeadTaskController::class, 'store'])->name('tenant.leads.tasks.store');
        Route::patch('leads/{lead}/tasks/{task}/status', [LeadTaskController::class, 'updateStatus'])->name('tenant.leads.tasks.status.update');
        Route::post('leads/{lead}/tasks/{task}/complete', [LeadTaskController::class, 'complete'])->name('tenant.leads.tasks.complete');

        Route::post('leads/{lead}/notes', [LeadNoteController::class, 'store'])->name('tenant.leads.notes.store');
        Route::patch('leads/{lead}/notes/{note}', [LeadNoteController::class, 'update'])->name('tenant.leads.notes.update');
        Route::delete('leads/{lead}/notes/{note}', [LeadNoteController::class, 'destroy'])->name('tenant.leads.notes.destroy');

        Route::post('leads/{lead}/documents', [LeadDocumentController::class, 'store'])->name('tenant.leads.documents.store');
        Route::get('leads/{lead}/documents/{document}/download', [LeadDocumentController::class, 'download'])->name('tenant.leads.documents.download');
        Route::delete('leads/{lead}/documents/{document}', [LeadDocumentController::class, 'destroy'])->name('tenant.leads.documents.destroy');

        Route::get('bookings', [BookingController::class, 'index'])->name('tenant.bookings.index');
        Route::get('bookings/create', [BookingController::class, 'create'])->name('tenant.bookings.create');
        Route::post('bookings', [BookingController::class, 'store'])->name('tenant.bookings.store');
        Route::post('bookings/{booking}/agreement', [BookingController::class, 'markAgreement'])->name('tenant.bookings.agreement.store');
        Route::post('bookings/{booking}/invoice', [BookingController::class, 'storeInvoice'])->name('tenant.bookings.invoice.store');

        Route::get('revenue', RevenueController::class)->name('tenant.revenue.index');
        Route::get('reports', ReportController::class)
            ->middleware('permission:reports.view')
            ->name('tenant.reports.index');
        Route::get('reports/export', ReportExportController::class)
            ->middleware('permission:reports.view')
            ->name('tenant.reports.export');
        Route::get('reports/print', ReportPrintController::class)
            ->middleware('permission:reports.view')
            ->name('tenant.reports.print');
        Route::get('reports/analytics', ReportAnalyticsController::class)
            ->middleware('permission:reports.view')
            ->name('tenant.reports.analytics');
        Route::get('payouts', [PayoutController::class, 'index'])->name('tenant.payouts.index');
        Route::post('payouts/{booking}/mark-paid', [PayoutController::class, 'markPaid'])->name('tenant.payouts.mark-paid');
        Route::get('invoices', [InvoiceController::class, 'index'])->name('tenant.invoices.index');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('tenant.invoices.store');
        Route::get('invoices/{booking}/pdf', [InvoiceController::class, 'downloadPdf'])->name('tenant.invoices.pdf');
        Route::patch('invoices/{booking}', [InvoiceController::class, 'update'])->name('tenant.invoices.update');
        Route::post('invoices/{booking}/mark-paid', [InvoiceController::class, 'markPaid'])->name('tenant.invoices.mark-paid');
        Route::get('integrations', IntegrationController::class)->name('tenant.integrations.index');
        Route::get('automations', AutomationController::class)
            ->middleware('permission:automations.view')
            ->name('tenant.automations.index');
        Route::get('automations/templates', [MessageTemplateController::class, 'index'])
            ->middleware('permission:automations.view')
            ->name('tenant.automations.templates');
        Route::get('automations/templates/create', [MessageTemplateController::class, 'create'])
            ->middleware('permission:automations.manage')
            ->name('tenant.automations.templates.create');
        Route::post('automations/templates', [MessageTemplateController::class, 'store'])
            ->middleware('permission:automations.manage')
            ->name('tenant.automations.templates.store');
        Route::get('automations/templates/{template}/edit', [MessageTemplateController::class, 'edit'])
            ->middleware('permission:automations.manage')
            ->whereNumber('template')
            ->name('tenant.automations.templates.edit');
        Route::patch('automations/templates/{template}', [MessageTemplateController::class, 'update'])
            ->middleware('permission:automations.manage')
            ->whereNumber('template')
            ->name('tenant.automations.templates.update');
        Route::delete('automations/templates/{template}', [MessageTemplateController::class, 'destroy'])
            ->middleware('permission:automations.manage')
            ->whereNumber('template')
            ->name('tenant.automations.templates.destroy');

        Route::get('automations/workflows', [AutomationWorkflowController::class, 'index'])
            ->middleware('permission:automations.view')
            ->name('tenant.automations.workflows');
        Route::post('automations/workflows', [AutomationWorkflowController::class, 'store'])
            ->middleware('permission:automations.manage')
            ->name('tenant.automations.workflows.store');
        Route::get('automations/workflows/{workflow}/edit', [AutomationWorkflowController::class, 'edit'])
            ->middleware('permission:automations.manage')
            ->whereNumber('workflow')
            ->name('tenant.automations.workflows.edit');
        Route::patch('automations/workflows/{workflow}', [AutomationWorkflowController::class, 'update'])
            ->middleware('permission:automations.manage')
            ->whereNumber('workflow')
            ->name('tenant.automations.workflows.update');
        Route::patch('automations/workflows/{workflow}/status', [AutomationWorkflowController::class, 'updateStatus'])
            ->middleware('permission:automations.manage')
            ->whereNumber('workflow')
            ->name('tenant.automations.workflows.status.update');
        Route::post('automations/workflows/{workflow}/test', [AutomationWorkflowController::class, 'test'])
            ->middleware('permission:automations.manage')
            ->whereNumber('workflow')
            ->name('tenant.automations.workflows.test');
        Route::delete('automations/workflows/{workflow}', [AutomationWorkflowController::class, 'destroy'])
            ->middleware('permission:automations.manage')
            ->whereNumber('workflow')
            ->name('tenant.automations.workflows.destroy');

        Route::get('teams', [SalesTeamController::class, 'index'])->name('tenant.teams.index');
        Route::post('teams', [SalesTeamController::class, 'store'])->middleware('permission:teams.manage')->name('tenant.teams.store');
        Route::post('teams/routing-rules', [LeadRoutingRuleController::class, 'store'])->middleware('permission:teams.manage')->name('tenant.teams.routing-rules.store');
        Route::patch('teams/routing-rules/{routingRule}', [LeadRoutingRuleController::class, 'update'])->middleware('permission:teams.manage')->whereNumber('routingRule')->name('tenant.teams.routing-rules.update');
        Route::delete('teams/routing-rules/{routingRule}', [LeadRoutingRuleController::class, 'destroy'])->middleware('permission:teams.manage')->whereNumber('routingRule')->name('tenant.teams.routing-rules.destroy');
        Route::get('teams/performance', [TeamPerformanceController::class, 'index'])->name('tenant.teams.performance.index');
        Route::get('teams/{team}/performance', [TeamPerformanceController::class, 'show'])->name('tenant.teams.performance.show');
        Route::get('teams/{team}', [SalesTeamController::class, 'show'])->name('tenant.teams.show');
        Route::patch('teams/{team}', [SalesTeamController::class, 'update'])->middleware('permission:teams.manage')->name('tenant.teams.update');
        Route::patch('teams/{team}/status', [SalesTeamController::class, 'updateStatus'])->middleware('permission:teams.manage')->name('tenant.teams.status.update');
        Route::delete('teams/{team}', [SalesTeamController::class, 'destroy'])->middleware('permission:teams.manage')->name('tenant.teams.destroy');
        Route::post('teams/{team}/members', [SalesTeamMemberController::class, 'store'])->middleware('permission:teams.manage')->name('tenant.teams.members.store');
        Route::delete('teams/{team}/members/{user}', [SalesTeamMemberController::class, 'destroy'])->middleware('permission:teams.manage')->name('tenant.teams.members.destroy');

        Route::get('team-chat/bootstrap', [TeamChatController::class, 'bootstrap'])->name('tenant.team-chat.bootstrap');
        Route::get('team-chat/conversations/{conversation}/messages', [TeamChatController::class, 'messages'])->name('tenant.team-chat.messages');
        Route::post('team-chat/messages', [TeamChatController::class, 'store'])->name('tenant.team-chat.messages.store');
        Route::get('team-chat/messages/{message}/attachments/{attachment}/download', [TeamChatController::class, 'downloadAttachment'])->name('tenant.team-chat.attachments.download');

        Route::get('activity-notifications', [ActivityDueNotificationController::class, 'index'])->name('tenant.activity-notifications.index');
        Route::post('activity-notifications/dismiss-popup', [ActivityDueNotificationController::class, 'dismissPopup'])->name('tenant.activity-notifications.dismiss-popup');
        Route::post('activity-notifications/read-all', [ActivityDueNotificationController::class, 'markAllRead'])->name('tenant.activity-notifications.read-all');
        Route::post('activity-notifications/{notification}/read', [ActivityDueNotificationController::class, 'markRead'])->name('tenant.activity-notifications.read');

        Route::get('activities', [ActivityController::class, 'index'])->name('tenant.activities.index');

        Route::patch('table-preferences/{tableKey}', [TablePreferencesController::class, 'update'])->name('tenant.table-preferences.update');
        Route::delete('table-bulk-delete/{tableKey}', [BulkTableDeleteController::class, 'destroy'])->name('tenant.table-bulk-delete');

        Route::get('tasks', [TaskController::class, 'index'])->name('tenant.tasks.index');
        Route::post('tasks', [TaskController::class, 'store'])->name('tenant.tasks.store');
        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tenant.tasks.status.update');
        Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])->name('tenant.tasks.complete');

        Route::get('whatsapp-web', [WhatsAppWebController::class, 'index'])->name('tenant.whatsapp-web.index');

        Route::get('follow-ups', [FollowUpController::class, 'index'])->name('tenant.follow-ups.index');
        Route::get('site-visits', [SiteVisitController::class, 'index'])->name('tenant.site-visits.index');
        Route::patch('scheduled-events/{scheduledEvent}/reschedule', [LeadScheduledEventController::class, 'reschedule'])->name('tenant.scheduled-events.reschedule');
        Route::post('scheduled-events/{scheduledEvent}/complete-follow-up', [LeadScheduledEventController::class, 'completeFollowUp'])->name('tenant.scheduled-events.complete-follow-up');
        Route::post('scheduled-events/{scheduledEvent}/complete-site-visit', [LeadScheduledEventController::class, 'completeSiteVisit'])->name('tenant.scheduled-events.complete-site-visit');

        Route::get('properties', [PropertyController::class, 'index'])->name('tenant.properties.index');
        Route::get('properties/create', [PropertyController::class, 'create'])->name('tenant.properties.create');
        Route::get('properties-export', PropertyExportController::class)->name('tenant.properties.export');
        Route::get('properties-import/sample', PropertyImportSampleController::class)->name('tenant.properties.import.sample');
        Route::post('properties-import', PropertyImportController::class)->name('tenant.properties.import');
        Route::post('properties', [PropertyController::class, 'store'])->name('tenant.properties.store');
        Route::patch('properties/{property}', [PropertyController::class, 'update'])->name('tenant.properties.update');
        Route::patch('properties/{property}/status', [PropertyController::class, 'updateStatus'])->name('tenant.properties.status.update');
        Route::patch('properties/{property}/website-visibility', [PropertyController::class, 'updateWebsiteVisibility'])->name('tenant.properties.website-visibility.update');
        Route::patch('properties/{property}/microsite', [PropertyController::class, 'updateMicrosite'])->name('tenant.properties.microsite.update');
        Route::get('properties/{property}/microsite/manage', [PropertyMicrositeCmsController::class, 'edit'])->name('tenant.properties.microsite.manage');
        Route::patch('properties/{property}/microsite/content', [PropertyMicrositeCmsController::class, 'update'])->name('tenant.properties.microsite.content.update');
        Route::delete('properties/{property}', [PropertyController::class, 'destroy'])->name('tenant.properties.destroy');

        Route::get('settings', [SettingsController::class, 'index'])->name('tenant.settings.index');
        Route::patch('settings/profile', [SettingsController::class, 'updateProfile'])->name('tenant.settings.profile.update');
        Route::patch('settings/company', [SettingsController::class, 'updateCompany'])->middleware('permission:settings.company')->name('tenant.settings.company.update');
        Route::put('settings/password', [SettingsController::class, 'updatePassword'])->name('tenant.settings.password.update');
        Route::post('settings/domains', [SettingsDomainController::class, 'upsert'])->middleware('permission:settings.company')->name('tenant.settings.domains.upsert');
        Route::post('settings/domains/verify', [SettingsDomainController::class, 'verify'])->middleware('permission:settings.company')->name('tenant.settings.domains.verify');
        Route::delete('settings/domains', [SettingsDomainController::class, 'destroy'])->middleware('permission:settings.company')->name('tenant.settings.domains.destroy');
        Route::get('settings/integrations/api', [SettingsLeadApiController::class, 'show'])
            ->middleware('permission:integrations.view')
            ->name('tenant.settings.integrations.api');
        Route::post('settings/integrations/api/regenerate', [SettingsLeadApiController::class, 'regenerate'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.api.regenerate');
        Route::get('settings/integrations/google-sheets', [SettingsGoogleSheetController::class, 'index'])
            ->middleware('permission:integrations.view')
            ->name('tenant.settings.integrations.google-sheets.index');
        Route::post('settings/integrations/google-sheets', [SettingsGoogleSheetController::class, 'store'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.google-sheets.store');
        Route::post('settings/integrations/google-sheets/sync-all', [SettingsGoogleSheetController::class, 'syncAll'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.google-sheets.sync-all');
        Route::get('settings/integrations/google-sheets/{googleSheet}', [SettingsGoogleSheetController::class, 'show'])
            ->middleware('permission:integrations.view')
            ->name('tenant.settings.integrations.google-sheets.show');
        Route::post('settings/integrations/google-sheets/{googleSheet}/verify', [SettingsGoogleSheetController::class, 'verify'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.google-sheets.verify');
        Route::put('settings/integrations/google-sheets/{googleSheet}/connect', [SettingsGoogleSheetController::class, 'connect'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.google-sheets.connect');
        Route::post('settings/integrations/google-sheets/{googleSheet}/sync', [SettingsGoogleSheetController::class, 'sync'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.google-sheets.sync');
        Route::post('settings/integrations/google-sheets/{googleSheet}/pause', [SettingsGoogleSheetController::class, 'pause'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.google-sheets.pause');
        Route::post('settings/integrations/google-sheets/{googleSheet}/resume', [SettingsGoogleSheetController::class, 'resume'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.google-sheets.resume');
        Route::delete('settings/integrations/google-sheets/{googleSheet}', [SettingsGoogleSheetController::class, 'destroy'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.google-sheets.destroy');
        Route::get('settings/integrations/facebook', [SettingsFacebookController::class, 'show'])
            ->middleware('permission:integrations.view')
            ->name('tenant.settings.integrations.facebook.show');
        Route::post('settings/integrations/facebook', [SettingsFacebookController::class, 'store'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.facebook.store');
        Route::post('settings/integrations/facebook/{facebookPage}/verify', [SettingsFacebookController::class, 'verify'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.facebook.verify');
        Route::put('settings/integrations/facebook/{facebookPage}/activate', [SettingsFacebookController::class, 'activate'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.facebook.activate');
        Route::post('settings/integrations/facebook/{facebookPage}/pause', [SettingsFacebookController::class, 'pause'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.facebook.pause');
        Route::post('settings/integrations/facebook/{facebookPage}/resume', [SettingsFacebookController::class, 'resume'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.facebook.resume');
        Route::delete('settings/integrations/facebook/{facebookPage}', [SettingsFacebookController::class, 'destroy'])
            ->middleware('permission:integrations.manage')
            ->name('tenant.settings.integrations.facebook.destroy');
        Route::get('settings/integrations/portals/{portal}', [SettingsPortalWebhookController::class, 'show'])
            ->middleware('permission:integrations.view')
            ->whereIn('portal', array_column(PropertyPortal::cases(), 'value'))
            ->name('tenant.settings.integrations.portal');
        Route::post('settings/integrations/portals/{portal}/regenerate', [SettingsPortalWebhookController::class, 'regenerate'])
            ->middleware('permission:integrations.manage')
            ->whereIn('portal', array_column(PropertyPortal::cases(), 'value'))
            ->name('tenant.settings.integrations.portal.regenerate');
        Route::post('settings/users', [SettingsUserController::class, 'store'])->middleware('permission:settings.users')->name('tenant.settings.users.store');
        Route::patch('settings/users/{user}', [SettingsUserController::class, 'update'])->middleware('permission:settings.users')->name('tenant.settings.users.update');
        Route::patch('settings/users/{user}/status', [SettingsUserController::class, 'updateStatus'])->middleware('permission:settings.users')->name('tenant.settings.users.status.update');
        Route::delete('settings/users/{user}', [SettingsUserController::class, 'destroy'])->middleware('permission:settings.users')->name('tenant.settings.users.destroy');
        Route::post('settings/roles', [SettingsRoleController::class, 'store'])->middleware('permission:settings.roles')->name('tenant.settings.roles.store');
        Route::patch('settings/roles/{role}', [SettingsRoleController::class, 'update'])->middleware('permission:settings.roles')->name('tenant.settings.roles.update');
        Route::patch('settings/roles/{role}/status', [SettingsRoleController::class, 'updateStatus'])->middleware('permission:settings.roles')->name('tenant.settings.roles.status.update');
        Route::delete('settings/roles/{role}', [SettingsRoleController::class, 'destroy'])->middleware('permission:settings.roles')->name('tenant.settings.roles.destroy');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('tenant.logout');
    });
});

Route::middleware([
    'web',
    PreventAccessFromCentralDomains::class,
    InitializeTenancyByDomain::class,
    EnsureVerifiedDomainPurpose::class.':crm',
    SetTenantUrlDefaults::class,
    PreventAccessBySuspendedTenant::class,
    PreventAccessByPausedSubscription::class,
])->group(function () {
    Route::get('/', function () {
        if (auth()->check()) {
            return redirect()->route('tenant.dashboard');
        }

        return redirect()->route('tenant.login');
    })->name('tenant.domain.crm.home');
});

Route::middleware([
    'web',
    PreventAccessFromCentralDomains::class,
    InitializeTenancyByDomain::class,
    EnsureVerifiedDomainPurpose::class.':website',
    SetTenantUrlDefaults::class,
    PreventAccessBySuspendedTenant::class,
])->group(function () {
    Route::get('projects/{slug}', [PropertyMicrositeController::class, 'show'])
        ->name('website.projects.microsite.show');
    Route::get('projects/{slug}/media/{key}', [PropertyMicrositeController::class, 'media'])
        ->where('key', '[A-Za-z0-9\-]+')
        ->name('website.projects.microsite.media');
    Route::post('projects/{slug}/enquire', [PropertyMicrositeController::class, 'enquire'])
        ->middleware('throttle:8,1')
        ->name('website.projects.microsite.enquire');
});
