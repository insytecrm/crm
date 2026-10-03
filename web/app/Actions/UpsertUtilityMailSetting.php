<?php

namespace App\Actions;

use App\Enums\UtilityCredentialsDeliveryMode;
use App\Enums\UtilityMailEncryption;
use App\Models\PlatformMailSetting;
use App\Models\UtilityMailSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpsertUtilityMailSetting
{
    /**
     * @param  array{
     *     host: string,
     *     port: int,
     *     username: string,
     *     password?: string|null,
     *     from_email: string,
     *     from_name: string,
     *     reply_to_email?: string|null,
     *     reply_to_name?: string|null,
     *     notes?: string|null,
     *     is_active?: bool,
     *     encryption: string,
     *     credentials_delivery_mode: string,
     *     remove_signature?: bool
     * }  $data
     */
    public function handle(
        PlatformMailSetting|UtilityMailSetting|null $setting,
        array $data,
        ?UploadedFile $signature = null,
        string $modelClass = UtilityMailSetting::class,
    ): PlatformMailSetting|UtilityMailSetting {
        $setting ??= $modelClass::query()->first() ?? new $modelClass;

        $setting->host = $data['host'];
        $setting->port = (int) $data['port'];
        $setting->username = $data['username'];
        $setting->from_email = $data['from_email'];
        $setting->from_name = $data['from_name'];
        $setting->encryption = UtilityMailEncryption::from($data['encryption']);
        $setting->credentials_delivery_mode = UtilityCredentialsDeliveryMode::from($data['credentials_delivery_mode']);

        if ($setting instanceof UtilityMailSetting) {
            $setting->reply_to_email = $data['reply_to_email'] ?? null;
            $setting->reply_to_name = $data['reply_to_name'] ?? null;
            $setting->notes = $data['notes'] ?? null;
            $setting->is_active = (bool) ($data['is_active'] ?? true);
        }

        if (filled($data['password'] ?? null)) {
            $setting->password = $data['password'];
        }

        if (($data['remove_signature'] ?? false) && filled($setting->signature_path)) {
            Storage::disk('public')->delete($setting->signature_path);
            $setting->signature_path = null;
        }

        if ($signature instanceof UploadedFile) {
            if (filled($setting->signature_path)) {
                Storage::disk('public')->delete($setting->signature_path);
            }

            $setting->signature_path = $signature->store('utility-signatures', 'public');
        }

        $setting->save();

        return $setting;
    }
}
