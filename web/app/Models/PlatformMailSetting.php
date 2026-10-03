<?php

namespace App\Models;

use App\Enums\UtilityCredentialsDeliveryMode;
use App\Enums\UtilityMailEncryption;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

#[Fillable([
    'host',
    'port',
    'username',
    'password',
    'from_email',
    'from_name',
    'encryption',
    'signature_path',
    'credentials_delivery_mode',
])]
#[Hidden(['password'])]
class PlatformMailSetting extends Model
{
    use CentralConnection;

    protected $attributes = [
        'port' => 587,
        'encryption' => 'tls',
        'credentials_delivery_mode' => 'ask',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'password' => 'encrypted',
            'encryption' => UtilityMailEncryption::class,
            'credentials_delivery_mode' => UtilityCredentialsDeliveryMode::class,
        ];
    }

    public static function current(): ?self
    {
        return static::query()->first();
    }

    public function isConfigured(): bool
    {
        return filled($this->host)
            && filled($this->username)
            && filled($this->password)
            && filled($this->from_email);
    }

    public function asksBeforeSending(): bool
    {
        return $this->credentials_delivery_mode === UtilityCredentialsDeliveryMode::Ask;
    }

    public function alwaysSends(): bool
    {
        return $this->credentials_delivery_mode === UtilityCredentialsDeliveryMode::Always;
    }
}
