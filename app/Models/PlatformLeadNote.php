<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

#[Fillable([
    'platform_lead_id',
    'user_id',
    'body',
])]
class PlatformLeadNote extends Model
{
    use CentralConnection;

    /**
     * @return BelongsTo<PlatformLead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(PlatformLead::class, 'platform_lead_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
