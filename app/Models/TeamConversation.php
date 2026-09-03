<?php

namespace App\Models;

use Database\Factories\TeamConversationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([])]
class TeamConversation extends Model
{
    /** @use HasFactory<TeamConversationFactory> */
    use HasFactory;

    /**
     * @return BelongsToMany<User, $this>
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_conversation_participants')
            ->withTimestamps();
    }

    /**
     * @return HasMany<TeamMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(TeamMessage::class);
    }

    public function includesUser(int $userId): bool
    {
        return $this->participants()->where('users.id', $userId)->exists();
    }
}
