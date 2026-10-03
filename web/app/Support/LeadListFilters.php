<?php

namespace App\Support;

use App\Enums\LeadBudget;
use App\Enums\LeadStatus;
use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LeadListFilters
{
    public function __construct(
        public readonly ?string $assignedTo = null,
        public readonly ?string $status = null,
        public readonly ?string $source = null,
        public readonly ?string $budget = null,
        public readonly ?string $propertyType = null,
        public readonly ?string $location = null,
        public readonly ?string $createdFrom = null,
        public readonly ?string $createdTo = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $assignedTo = filled($request->input('assigned_to'))
            ? (string) $request->input('assigned_to')
            : null;

        $status = filled($request->input('status'))
            ? LeadStatus::tryFrom((string) $request->input('status'))?->value
            : null;

        $budget = filled($request->input('budget'))
            ? LeadBudget::tryFrom((string) $request->input('budget'))?->value
            : null;

        $propertyType = filled($request->input('property_type'))
            ? PropertyType::tryFrom((string) $request->input('property_type'))?->value
            : null;

        return new self(
            assignedTo: $assignedTo,
            status: $status,
            source: filled($request->input('source')) ? (string) $request->input('source') : null,
            budget: $budget,
            propertyType: $propertyType,
            location: filled($request->input('location')) ? trim((string) $request->input('location')) : null,
            createdFrom: filled($request->input('created_from')) ? (string) $request->input('created_from') : null,
            createdTo: filled($request->input('created_to')) ? (string) $request->input('created_to') : null,
        );
    }

    public function isActive(): bool
    {
        return $this->activeCount() > 0;
    }

    public function activeCount(): int
    {
        return collect([
            $this->assignedTo,
            $this->status,
            $this->source,
            $this->budget,
            $this->propertyType,
            $this->location,
            $this->createdFrom,
            $this->createdTo,
        ])->filter(fn (mixed $value): bool => filled($value))->count();
    }

    /**
     * @return array<string, mixed>
     */
    public function toQueryArray(): array
    {
        return array_filter([
            'assigned_to' => $this->assignedTo,
            'status' => $this->status,
            'source' => $this->source,
            'budget' => $this->budget,
            'property_type' => $this->propertyType,
            'location' => $this->location,
            'created_from' => $this->createdFrom,
            'created_to' => $this->createdTo,
        ], fn (mixed $value): bool => filled($value));
    }

    public function applyTo(Builder $query): Builder
    {
        return $query
            ->when($this->assignedTo === 'unassigned', fn (Builder $query): Builder => $query->whereNull('assigned_to_id'))
            ->when(
                $this->assignedTo !== null && $this->assignedTo !== 'unassigned',
                fn (Builder $query): Builder => $query->where('assigned_to_id', (int) $this->assignedTo),
            )
            ->when($this->status !== null, fn (Builder $query): Builder => $query->where('status', $this->status))
            ->when($this->source !== null, fn (Builder $query): Builder => $query->where('source', $this->source))
            ->when($this->budget !== null, fn (Builder $query): Builder => $query->where('budget', $this->budget))
            ->when($this->propertyType !== null, fn (Builder $query): Builder => $query->where('property_type', $this->propertyType))
            ->when($this->location !== null, function (Builder $query): Builder {
                $escaped = addcslashes($this->location, '%_\\');

                return $query->where('location', 'like', "%{$escaped}%");
            })
            ->when($this->createdFrom !== null || $this->createdTo !== null, function (Builder $query): Builder {
                QueryableDate::constrain($query, 'created_at', $this->createdFrom, $this->createdTo);

                return $query;
            });
    }
}
