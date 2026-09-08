<?php

namespace App\Enums;

use App\Models\User;

enum SettingsGroup: string
{
    case Account = 'account';
    case Workspace = 'workspace';
    case Team = 'team';
    case Integrations = 'integrations';

    public function label(): string
    {
        return match ($this) {
            self::Account => __('My Account'),
            self::Workspace => __('Workspace'),
            self::Team => __('Team'),
            self::Integrations => __('Integrations'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Account => __('Your personal profile and password'),
            self::Workspace => __('Company details and custom domains'),
            self::Team => __('Users and role permissions'),
            self::Integrations => __('Connect lead sources and tools'),
        };
    }

    /**
     * @return list<SettingsTab>
     */
    public function tabs(): array
    {
        return match ($this) {
            self::Account => [SettingsTab::Profile, SettingsTab::Security],
            self::Workspace => [SettingsTab::Company, SettingsTab::Domains],
            self::Team => [SettingsTab::Users, SettingsTab::Roles],
            self::Integrations => [SettingsTab::Integrations],
        };
    }

    /**
     * @return list<self>
     */
    public static function visibleFor(?User $user): array
    {
        $visibleTabs = SettingsTab::visibleFor($user);

        return array_values(array_filter(
            self::cases(),
            fn (self $group): bool => $group->hasVisibleTab($visibleTabs),
        ));
    }

    /**
     * @param  list<SettingsTab>  $visibleTabs
     */
    public function hasVisibleTab(array $visibleTabs): bool
    {
        foreach ($this->tabs() as $tab) {
            if (in_array($tab, $visibleTabs, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<SettingsTab>  $visibleTabs
     * @return list<SettingsTab>
     */
    public function visibleTabs(array $visibleTabs): array
    {
        return array_values(array_filter(
            $this->tabs(),
            fn (SettingsTab $tab): bool => in_array($tab, $visibleTabs, true),
        ));
    }
}
