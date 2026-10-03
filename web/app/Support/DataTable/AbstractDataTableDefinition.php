<?php

namespace App\Support\DataTable;

use App\Contracts\DataTableDefinition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class AbstractDataTableDefinition implements DataTableDefinition
{
    public function bulkDelete(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = $this->modelClass();

        return $modelClass::query()->whereIn('id', $ids)->delete();
    }

    /**
     * @return array<string, string>
     */
    public function redirectParameters(Request $request): array
    {
        return $request->only(array_merge(
            ['filter', 'stage', 'search', 'kind', 'tab'],
            array_keys($request->query()),
        ));
    }
}
