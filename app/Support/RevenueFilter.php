<?php

namespace App\Support;

use Illuminate\Http\Request;

class RevenueFilter
{
    public function __construct(
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
        public readonly ?string $developer = null,
        public readonly ?int $propertyId = null,
        public readonly ?int $salespersonId = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            dateFrom: filled($request->input('from')) ? (string) $request->input('from') : null,
            dateTo: filled($request->input('to')) ? (string) $request->input('to') : null,
            developer: filled($request->input('developer')) ? (string) $request->input('developer') : null,
            propertyId: filled($request->input('property')) ? (int) $request->input('property') : null,
            salespersonId: filled($request->input('salesperson')) ? (int) $request->input('salesperson') : null,
        );
    }

    public function isActive(): bool
    {
        return $this->dateFrom !== null
            || $this->dateTo !== null
            || $this->developer !== null
            || $this->propertyId !== null
            || $this->salespersonId !== null;
    }

    public function activeCount(): int
    {
        return collect([
            $this->dateFrom,
            $this->dateTo,
            $this->developer,
            $this->propertyId,
            $this->salespersonId,
        ])->filter(fn (mixed $value): bool => filled($value))->count();
    }

    /**
     * @return array<string, mixed>
     */
    public function toQueryArray(): array
    {
        return array_filter([
            'from' => $this->dateFrom,
            'to' => $this->dateTo,
            'developer' => $this->developer,
            'property' => $this->propertyId,
            'salesperson' => $this->salespersonId,
        ], fn (mixed $value): bool => filled($value));
    }
}
