<?php

namespace App\Enums;

enum TenantPermission: string
{
    case DashboardView = 'dashboard.view';

    case LeadsView = 'leads.view';
    case LeadsCreate = 'leads.create';
    case LeadsUpdate = 'leads.update';
    case LeadsDelete = 'leads.delete';
    case LeadsExport = 'leads.export';
    case LeadsImport = 'leads.import';

    case ActivitiesView = 'activities.view';
    case ActivitiesManage = 'activities.manage';

    case TasksView = 'tasks.view';
    case TasksManage = 'tasks.manage';

    case PropertiesView = 'properties.view';
    case PropertiesManage = 'properties.manage';

    case BookingsView = 'bookings.view';
    case BookingsManage = 'bookings.manage';

    case RevenueView = 'revenue.view';
    case PayoutsManage = 'payouts.manage';
    case InvoicesManage = 'invoices.manage';

    case IntegrationsView = 'integrations.view';
    case IntegrationsManage = 'integrations.manage';

    case TeamChatUse = 'team_chat.use';

    case TeamsView = 'teams.view';
    case TeamsManage = 'teams.manage';

    case SettingsCompany = 'settings.company';
    case SettingsUsers = 'settings.users';
    case SettingsRoles = 'settings.roles';

    public function label(): string
    {
        return match ($this) {
            self::DashboardView => __('View Dashboard'),
            self::LeadsView => __('View Leads'),
            self::LeadsCreate => __('Create Leads'),
            self::LeadsUpdate => __('Edit Leads'),
            self::LeadsDelete => __('Delete Leads'),
            self::LeadsExport => __('Export Leads'),
            self::LeadsImport => __('Import Leads'),
            self::ActivitiesView => __('View Activities'),
            self::ActivitiesManage => __('Manage Follow-ups & Site Visits'),
            self::TasksView => __('View Tasks'),
            self::TasksManage => __('Manage Tasks'),
            self::PropertiesView => __('View Properties'),
            self::PropertiesManage => __('Manage Properties'),
            self::BookingsView => __('View Bookings'),
            self::BookingsManage => __('Manage Bookings'),
            self::RevenueView => __('View Revenue'),
            self::PayoutsManage => __('Manage Payouts'),
            self::InvoicesManage => __('Manage Invoices'),
            self::IntegrationsView => __('View Integrations'),
            self::IntegrationsManage => __('Manage Integrations'),
            self::TeamChatUse => __('Use Team Inbox'),
            self::TeamsView => __('View Teams'),
            self::TeamsManage => __('Manage Teams'),
            self::SettingsCompany => __('Manage Company Settings'),
            self::SettingsUsers => __('Manage Users'),
            self::SettingsRoles => __('Manage Roles & Permissions'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::DashboardView => __('Access the main dashboard overview.'),
            self::LeadsView => __('Browse lead lists and open lead details.'),
            self::LeadsCreate => __('Add new leads from the CRM.'),
            self::LeadsUpdate => __('Edit lead details, status, and notes.'),
            self::LeadsDelete => __('Delete or bulk-delete leads.'),
            self::LeadsExport => __('Export lead data to CSV.'),
            self::LeadsImport => __('Import leads from a file.'),
            self::ActivitiesView => __('View the activities home and calendars.'),
            self::ActivitiesManage => __('Schedule, reschedule, and complete follow-ups and site visits.'),
            self::TasksView => __('View the tasks list.'),
            self::TasksManage => __('Create, assign, and complete tasks.'),
            self::PropertiesView => __('Browse the property catalog.'),
            self::PropertiesManage => __('Create and edit property listings.'),
            self::BookingsView => __('View bookings and booking details.'),
            self::BookingsManage => __('Create bookings, mark agreements, and generate invoices.'),
            self::RevenueView => __('View revenue statistics and summaries.'),
            self::PayoutsManage => __('View payouts and mark them as paid.'),
            self::InvoicesManage => __('View, edit, and download invoices.'),
            self::IntegrationsView => __('View integration settings.'),
            self::IntegrationsManage => __('Connect and configure integrations.'),
            self::TeamChatUse => __('Send and read messages in the team inbox.'),
            self::TeamsView => __('Browse sales teams and open team details.'),
            self::TeamsManage => __('Create, edit, archive teams and manage members.'),
            self::SettingsCompany => __('Update workspace name and company details.'),
            self::SettingsUsers => __('Invite and manage workspace users.'),
            self::SettingsRoles => __('Create roles and configure permission switches.'),
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::DashboardView => __('Dashboard'),
            self::LeadsView, self::LeadsCreate, self::LeadsUpdate, self::LeadsDelete, self::LeadsExport, self::LeadsImport => __('Leads'),
            self::ActivitiesView, self::ActivitiesManage => __('Activities'),
            self::TasksView, self::TasksManage => __('Tasks'),
            self::PropertiesView, self::PropertiesManage => __('Properties'),
            self::BookingsView, self::BookingsManage => __('Bookings'),
            self::RevenueView, self::PayoutsManage, self::InvoicesManage => __('Revenue'),
            self::IntegrationsView, self::IntegrationsManage => __('Integrations'),
            self::TeamChatUse => __('Team Inbox'),
            self::TeamsView, self::TeamsManage => __('Teams'),
            self::SettingsCompany, self::SettingsUsers, self::SettingsRoles => __('Settings'),
        };
    }

    /**
     * @return array<string, list<self>>
     */
    public static function grouped(): array
    {
        $groups = [];

        foreach (self::cases() as $permission) {
            $groups[$permission->group()][] = $permission;
        }

        return $groups;
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_map(
            fn (self $permission): string => $permission->value,
            self::cases(),
        );
    }
}
