<?php

namespace App\Models;

use App\Enums\LeadRoutingDistribution;
use Database\Factories\LeadRoutingRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'source',
    'sub_source',
    'sales_team_id',
    'distribution',
    'is_active',
    'distribution_cursor',
    'created_by_id',
])]
class LeadRoutingRule extends Model
{
    /** @use HasFactory<LeadRoutingRuleFactory> */
    use HasFactory;

    protected $attributes = [
        'is_active' => true,
        'distribution_cursor' => 0,
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'distribution' => LeadRoutingDistribution::class,
            'is_active' => 'boolean',
            'distribution_cursor' => 'integer',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @return BelongsTo<SalesTeam, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(SalesTeam::class, 'sales_team_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('weight')
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function summary(): string
    {
        $source = $this->source;
        $sub = filled($this->sub_source) ? ' › '.$this->sub_source : '';
        $team = $this->relationLoaded('team') ? ($this->team?->name ?? __('Team')) : __('Team');

        return $source.$sub.' → '.$team.' · '.$this->distribution->label();
    }
}
