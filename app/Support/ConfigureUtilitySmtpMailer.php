<?php

namespace App\Support;

use App\Enums\UtilityMailEncryption;
use App\Models\PlatformMailSetting;
use App\Models\UtilityMailSetting;
use Illuminate\Support\Facades\Config;

class ConfigureUtilitySmtpMailer
{
    public const MailerName = 'utility_smtp';

    public function handle(PlatformMailSetting|UtilityMailSetting $setting): string
    {
        $encryption = $setting->encryption instanceof UtilityMailEncryption
            ? $setting->encryption
            : UtilityMailEncryption::Tls;

        Config::set('mail.mailers.'.self::MailerName, [
            'transport' => 'smtp',
            'scheme' => $encryption->scheme(),
            'host' => $setting->host,
            'port' => $setting->port,
            'username' => $setting->username,
            'password' => $setting->password,
            'timeout' => null,
        ]);

        Config::set('mail.from', [
            'address' => $setting->from_email,
            'name' => $setting->from_name,
        ]);

        return self::MailerName;
    }
}
