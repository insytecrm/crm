<?php

namespace App\Enums;

enum TenantUtilityEmailTemplateType: string
{
    case TeamUserWelcome = 'team_user_welcome';
    case TeamUserPasswordReset = 'team_user_password_reset';

    public function label(): string
    {
        return match ($this) {
            self::TeamUserWelcome => __('Team user welcome'),
            self::TeamUserPasswordReset => __('Team user password reset'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::TeamUserWelcome => __('Sent when an admin creates a new team user.'),
            self::TeamUserPasswordReset => __('Sent when an admin resets a team user’s password.'),
        };
    }

    /**
     * @return list<string>
     */
    public function mergeVariables(): array
    {
        return [
            'user.name',
            'user.email',
            'user.password',
            'login.url',
            'company.name',
        ];
    }

    /**
     * @return array{subject: string, body: string}
     */
    public function defaults(): array
    {
        return match ($this) {
            self::TeamUserWelcome => [
                'subject' => 'Welcome to {{company.name}} on InSyte CRM',
                'body' => "Hello {{user.name}},\n\nAn account has been created for you on {{company.name}}.\n\nLogin URL: {{login.url}}\nEmail: {{user.email}}\nPassword: {{user.password}}\n\nPlease sign in and change your password after first login.\n\n— {{company.name}}",
            ],
            self::TeamUserPasswordReset => [
                'subject' => 'Your {{company.name}} password was reset',
                'body' => "Hello {{user.name}},\n\nYour password for {{company.name}} was reset by an administrator.\n\nLogin URL: {{login.url}}\nEmail: {{user.email}}\nTemporary password: {{user.password}}\n\nPlease sign in and change your password.\n\n— {{company.name}}",
            ],
        };
    }
}
