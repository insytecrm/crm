<?php

namespace App\Models;

use App\Enums\UtilityCredentialsDeliveryMode;
use App\Enums\UtilityMailEncryption;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'host',
    'port',
    'username',
    'password',
    'from_email',
    'from_name',
    'reply_to_email',
    'reply_to_name',
    'notes',
    'is_active',
    'encryption',
    'signature_path',
    'credentials_delivery_mode',
])]
#[Hidden(['password'])]
class UtilityMailSetting extends Model
{
    protected $attributes = [
        'port' => 587,
        'encryption' => 'tls',
        'credentials_delivery_mode' => 'ask',
        'is_active' => true,
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
            'is_active' => 'boolean',
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
            && filled($this->from_email)
            && (bool) $this->is_active;
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
