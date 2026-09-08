<?php

namespace App\Queries;

use App\Enums\PropertyFilter;
use App\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PropertyListing
{
    /**
     * @return array{
     *     total: int,
     *     active: int,
     *     inactive: int,
     *     featured: int,
     * }
     */
    public function statistics(): array
    {
        $row = Property::query()
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) as active')
            ->selectRaw('COALESCE(SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END), 0) as inactive')
            ->selectRaw('COALESCE(SUM(CASE WHEN show_on_website = 1 THEN 1 ELSE 0 END), 0) as featured')
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'active' => (int) ($row->active ?? 0),
            'inactive' => (int) ($row->inactive ?? 0),
            'featured' => (int) ($row->featured ?? 0),
        ];
    }

    public function paginate(PropertyFilter $filter, string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return $this->baseQuery($filter, $search)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Builder<Property>
     */
    private function baseQuery(PropertyFilter $filter, string $search): Builder
    {
        return Property::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('project_name', 'like', "%{$search}%")
                        ->orWhere('developer_name', 'like', "%{$search}%")
                        ->orWhere('project_location', 'like', "%{$search}%")
                        ->orWhere('property_type', 'like', "%{$search}%");
                });
            })
            ->when($filter === PropertyFilter::Active, fn (Builder $query): Builder => $query->where('is_active', true))
            ->when($filter === PropertyFilter::Inactive, fn (Builder $query): Builder => $query->where('is_active', false))
            ->when($filter === PropertyFilter::Featured, fn (Builder $query): Builder => $query->where('show_on_website', true));
    }
}
