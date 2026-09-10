<?php

namespace App\Actions;

use App\Models\Property;
use App\Support\PropertyCsvSchema;

class ImportPropertyFromCsvRow
{
    /**
     * @param  list<string|null>  $cells
     */
    public function handle(array $cells, int $createdById): Property
    {
        $attributes = PropertyCsvSchema::mapImportRow($cells);

        return Property::query()->create([
            ...$attributes,
            'created_by_id' => $createdById,
            'is_active' => true,
            'show_on_website' => false,
            'microsite_enabled' => false,
        ]);
    }
}
