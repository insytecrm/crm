<?php

namespace App\Support\DataTable;

use App\Models\TableCustomColumnValue;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DataTableViewData
{
    /**
     * @param  Collection<int, mixed>|array<int, mixed>|LengthAwarePaginator  $items
     * @return array{
     *     dataTableKey: string,
     *     dataTablePreferences: array,
     *     dataTableColumnLabels: array<string, string>,
     *     dataTableItemIds: list<int>,
     *     dataTableBulkDeleteUrl: string,
     *     dataTableBulkDeleteParam: string,
     *     dataTableCustomValues: array<int, array<string, string>>,
     *     dataTableCanBulkDelete: bool,
     * }
     */
    public static function for(
        User $user,
        string $tableKey,
        Collection|array|LengthAwarePaginator $items,
        string $idAttribute = 'id',
        ?string $listingKey = null,
    ): array {
        $definition = DataTableRegistry::get($tableKey);
        $collection = match (true) {
            $items instanceof LengthAwarePaginator => $items->getCollection(),
            $items instanceof Collection => $items,
            default => collect($items),
        };

        $itemIds = $collection
            ->map(function (mixed $item) use ($idAttribute): ?int {
                if (is_array($item)) {
                    return isset($item[$idAttribute]) ? (int) $item[$idAttribute] : null;
                }

                return (int) $item->getAttribute($idAttribute);
            })
            ->filter()
            ->values()
            ->all();

        $customValues = TableCustomColumnValue::query()
            ->where('table_key', $tableKey)
            ->whereIn('record_id', $itemIds)
            ->get()
            ->groupBy('record_id')
            ->map(fn (Collection $group): array => $group->pluck('value', 'column_key')->all())
            ->all();

        return [
            'dataTableKey' => $tableKey,
            'dataTableListing' => $listingKey,
            'dataTablePreferences' => $user->dataTablePreferences($tableKey, $definition, $listingKey),
            'dataTableColumnLabels' => $definition->columnLabels(),
            'dataTableDefaultColumns' => $definition->defaultColumns(),
            'dataTableRequiredColumns' => $definition->requiredColumns(),
            'dataTableItemIds' => $itemIds,
            'dataTableBulkDeleteUrl' => route('tenant.table-bulk-delete', $tableKey),
            'dataTableBulkDeleteParam' => $definition->bulkDeleteParameterName(),
            'dataTableCustomValues' => $customValues,
            'dataTableCanBulkDelete' => $definition->authorizeBulkDelete($user),
        ];
    }
}
