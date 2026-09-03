<?php

namespace App\Http\Requests\Tenant;

use App\Support\DataTable\DataTableRegistry;
use Illuminate\Foundation\Http\FormRequest;

class BulkTableDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tableKey = (string) $this->route('tableKey');
        $definition = DataTableRegistry::get($tableKey);

        return $definition->authorizeBulkDelete($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tableKey = (string) $this->route('tableKey');
        $definition = DataTableRegistry::get($tableKey);
        $parameter = $definition->bulkDeleteParameterName();

        return [
            $parameter => ['required', 'array', 'min:1'],
            "{$parameter}.*" => ['integer', 'distinct'],
        ];
    }
}
