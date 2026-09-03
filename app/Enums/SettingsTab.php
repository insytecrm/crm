<?php

namespace App\Enums;

use App\Models\User;

enum SettingsTab: string
{
    case Profile = 'profile';
    case Company = 'company';
    case Security = 'security';
    case Notifications = 'notifications';
    case Users = 'users';
    case Roles = 'roles';

    public function label(): string
    {
        return match ($this) {
            self::Profile => __('Profile'),
            self::Company => __('Company'),
            self::Security => __('Security'),
            self::Notifications => __('Notifications'),
            self::Users => __('Users'),
            self::Roles => __('Roles & Permissions'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Profile => __('Your personal account details'),
            self::Company => __('Workspace name and contact information'),
            self::Security => __('Password and account protection'),
            self::Notifications => __('Email and activity alerts'),
            self::Users => __('Invite and manage workspace users'),
            self::Roles => __('Create roles and configure permission switches'),
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
