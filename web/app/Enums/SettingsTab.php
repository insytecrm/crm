<?php

namespace App\Enums;

use App\Models\User;
use App\Support\Platform\TenantPlanAccess;

enum SettingsTab: string
{
    case Profile = 'profile';
    case Company = 'company';
    case Security = 'security';
    case Users = 'users';
    case Roles = 'roles';
    case Domains = 'domains';
    case Integrations = 'integrations';

    public function label(): string
    {
        return match ($this) {
            self::Profile => __('Profile'),
            self::Company => __('Company'),
            self::Security => __('Security'),
            self::Users => __('Users'),
            self::Roles => __('Roles & Permissions'),
            self::Domains => __('Domains'),
            self::Integrations => __('Integrations'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Profile => __('Your personal account details'),
            self::Company => __('Workspace name and contact information'),
            self::Security => __('Password and account protection'),
            self::Users => __('Invite and manage workspace users'),
            self::Roles => __('Create roles and configure permission switches'),
            self::Domains => __('Manage custom domains for CRM and website'),
            self::Integrations => __('Connect lead sources and third-party tools'),
        };
    }

    public function group(): SettingsGroup
    {
        return match ($this) {
            self::Profile, self::Security => SettingsGroup::Account,
            self::Company, self::Domains => SettingsGroup::Workspace,
            self::Users, self::Roles => SettingsGroup::Team,
            self::Integrations => SettingsGroup::Integrations,
        };
    }

    /**
     * @return list<self>
     */
    public static function visibleFor(?User $user): array
    {
        return array_values(array_filter(
            self::cases(),
            function (self $tab) use ($user): bool {
                if ($user === null) {
                    return false;
                }

                return match ($tab) {
                    self::Users => $user->hasPermission(TenantPermission::SettingsUsers),
                    self::Roles => $user->hasPermission(TenantPermission::SettingsRoles),
                    self::Company => $user->hasPermission(TenantPermission::SettingsCompany),
                    self::Domains => $user->hasPermission(TenantPermission::SettingsCompany)
                        && app(TenantPlanAccess::class)->hasFeature(PlanFeature::CustomDomains),
                    self::Integrations => $user->hasPermission(TenantPermission::IntegrationsView)
                        && app(TenantPlanAccess::class)->hasFeature(PlanFeature::Integrations),
                    default => true,
                };
            },
        ));
    }

    public static function fromQuery(?string $tab): self
    {
        if ($tab === null) {
            return self::Profile;
        }

        return self::tryFrom($tab) ?? self::Profile;
    }
}
