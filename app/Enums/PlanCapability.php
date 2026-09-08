<?php

namespace App\Enums;

enum PlanCapability: string
{
    case CrmImport = 'crm.import';
    case CrmExport = 'crm.export';
    case CrmDuplicates = 'crm.duplicates';
    case CrmDocuments = 'crm.documents';

    case AiChat = 'ai.chat';
    case AiSearchLeads = 'ai.search_leads';
    case AiListToday = 'ai.list_today';
    case AiScheduleFollowUp = 'ai.schedule_follow_up';
    case AiScheduleSiteVisit = 'ai.schedule_site_visit';
    case AiCompleteActivities = 'ai.complete_activities';
    case AiTasks = 'ai.tasks';
    case AiAddNote = 'ai.add_note';
    case AiTabLeadInsights = 'ai.tab_lead_insights';
    case AiTabContent = 'ai.tab_content';
    case AiTabTaskAssistant = 'ai.tab_task_assistant';
    case AiTabReports = 'ai.tab_reports';

    case AutomationsActionCreateTask = 'automations.action_create_task';
    case AutomationsActionAddNote = 'automations.action_add_note';
    case AutomationsActionChangeStatus = 'automations.action_change_status';
    case AutomationsActionScheduleFollowUp = 'automations.action_schedule_follow_up';
    case AutomationsActionSendWhatsApp = 'automations.action_send_whatsapp';
    case AutomationsActionSendEmail = 'automations.action_send_email';
    case AutomationsTemplates = 'automations.templates';

    case ReportsAnalytics = 'reports.analytics';
    case ReportsExport = 'reports.export';
    case ReportsPrint = 'reports.print';

    case IntegrationApi = 'integration.api';
    case IntegrationGoogleSheets = 'integration.google_sheets';
    case IntegrationFacebook = 'integration.facebook';
    case IntegrationNinetyNineAcres = 'integration.99acres';
    case IntegrationHousing = 'integration.housing';
    case IntegrationMagicBricks = 'integration.magicbricks';
    case IntegrationNoBroker = 'integration.nobroker';
    case IntegrationWhatsApp = 'integration.whatsapp';
    case IntegrationEmail = 'integration.email';
    case IntegrationCalendar = 'integration.calendar';

    public function label(): string
    {
        return match ($this) {
            self::CrmImport => __('Lead import'),
            self::CrmExport => __('Lead export'),
            self::CrmDuplicates => __('Duplicate leads'),
            self::CrmDocuments => __('Lead documents'),
            self::AiChat => __('AI Chat'),
            self::AiSearchLeads => __('Search and open leads'),
            self::AiListToday => __('Today’s agenda'),
            self::AiScheduleFollowUp => __('Schedule follow-ups'),
            self::AiScheduleSiteVisit => __('Schedule site visits'),
            self::AiCompleteActivities => __('Complete follow-ups and visits'),
            self::AiTasks => __('Create and complete tasks'),
            self::AiAddNote => __('Add notes'),
            self::AiTabLeadInsights => __('Lead Insights tab'),
            self::AiTabContent => __('Content & Scripts tab'),
            self::AiTabTaskAssistant => __('Task Assistant tab'),
            self::AiTabReports => __('Reports & Analysis tab'),
            self::AutomationsActionCreateTask => __('Create task action'),
            self::AutomationsActionAddNote => __('Add note action'),
            self::AutomationsActionChangeStatus => __('Change status action'),
            self::AutomationsActionScheduleFollowUp => __('Schedule follow-up action'),
            self::AutomationsActionSendWhatsApp => __('Send WhatsApp action'),
            self::AutomationsActionSendEmail => __('Send email action'),
            self::AutomationsTemplates => __('Message templates'),
            self::ReportsAnalytics => __('Analytics'),
            self::ReportsExport => __('Export reports'),
            self::ReportsPrint => __('Print reports'),
            self::IntegrationApi => __('Lead API'),
            self::IntegrationGoogleSheets => __('Google Sheets'),
            self::IntegrationFacebook => __('Facebook Lead Ads'),
            self::IntegrationNinetyNineAcres => __('99acres'),
            self::IntegrationHousing => __('Housing.com'),
            self::IntegrationMagicBricks => __('MagicBricks'),
            self::IntegrationNoBroker => __('NoBroker'),
            self::IntegrationWhatsApp => __('WhatsApp Business'),
            self::IntegrationEmail => __('Email'),
            self::IntegrationCalendar => __('Calendar'),
        };
    }

    public function feature(): PlanFeature
    {
        return match ($this) {
            self::CrmImport, self::CrmExport, self::CrmDuplicates, self::CrmDocuments => PlanFeature::Crm,
            self::AiChat, self::AiSearchLeads, self::AiListToday, self::AiScheduleFollowUp, self::AiScheduleSiteVisit, self::AiCompleteActivities, self::AiTasks, self::AiAddNote, self::AiTabLeadInsights, self::AiTabContent, self::AiTabTaskAssistant, self::AiTabReports => PlanFeature::InsyteAi,
            self::AutomationsActionCreateTask, self::AutomationsActionAddNote, self::AutomationsActionChangeStatus, self::AutomationsActionScheduleFollowUp, self::AutomationsActionSendWhatsApp, self::AutomationsActionSendEmail, self::AutomationsTemplates => PlanFeature::Automations,
            self::ReportsAnalytics, self::ReportsExport, self::ReportsPrint => PlanFeature::Reports,
            self::IntegrationApi, self::IntegrationGoogleSheets, self::IntegrationFacebook, self::IntegrationNinetyNineAcres, self::IntegrationHousing, self::IntegrationMagicBricks, self::IntegrationNoBroker, self::IntegrationWhatsApp, self::IntegrationEmail, self::IntegrationCalendar => PlanFeature::Integrations,
        };
    }

    public function isShipped(): bool
    {
        return match ($this) {
            self::AiTabLeadInsights, self::AiTabContent, self::AiTabTaskAssistant, self::AiTabReports, self::IntegrationWhatsApp, self::IntegrationEmail, self::IntegrationCalendar => false,
            default => true,
        };
    }

    /**
     * @return list<self>
     */
    public static function forFeature(PlanFeature $feature): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $capability): bool => $capability->feature() === $feature,
        ));
    }

    public static function fromInsyteAiTool(InsyteAiTool $tool): self
    {
        return match ($tool) {
            InsyteAiTool::SearchLeads, InsyteAiTool::GetLead => self::AiSearchLeads,
            InsyteAiTool::ListToday => self::AiListToday,
            InsyteAiTool::ScheduleFollowUp => self::AiScheduleFollowUp,
            InsyteAiTool::ScheduleSiteVisit => self::AiScheduleSiteVisit,
            InsyteAiTool::CompleteFollowUp, InsyteAiTool::CompleteSiteVisit => self::AiCompleteActivities,
            InsyteAiTool::CreateTask, InsyteAiTool::CompleteTask => self::AiTasks,
            InsyteAiTool::AddNote => self::AiAddNote,
        };
    }

    public static function fromAutomationAction(AutomationActionType $type): ?self
    {
        return match ($type) {
            AutomationActionType::CreateTask => self::AutomationsActionCreateTask,
            AutomationActionType::AddNote => self::AutomationsActionAddNote,
            AutomationActionType::ChangeStatus => self::AutomationsActionChangeStatus,
            AutomationActionType::ScheduleFollowUp => self::AutomationsActionScheduleFollowUp,
            AutomationActionType::SendWhatsApp => self::AutomationsActionSendWhatsApp,
            AutomationActionType::SendEmail => self::AutomationsActionSendEmail,
            default => null,
        };
    }

    public static function fromAiOsTab(AiOsTab $tab): ?self
    {
        return match ($tab) {
            AiOsTab::Chat => self::AiChat,
            AiOsTab::LeadInsights => self::AiTabLeadInsights,
            AiOsTab::Content => self::AiTabContent,
            AiOsTab::TaskAssistant => self::AiTabTaskAssistant,
            AiOsTab::Reports => self::AiTabReports,
        };
    }
}
