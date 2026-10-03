<?php

namespace App\Models;

use Database\Factories\TeamMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'team_conversation_id',
    'user_id',
    'body',
    'attachments',
])]
class TeamMessage extends Model
{
    /** @use HasFactory<TeamMessageFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'attachments' => 'array',
        ];
    }

    /**
     * @return BelongsTo<TeamConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(TeamConversation::class, 'team_conversation_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
