<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface DataTableDefinition
{
    public function key(): string;

    /**
     * @return array<string, bool>
     */
    public function defaultColumns(): array;

    /**
     * @return list<string>
     */
    public function requiredColumns(): array;

    /**
     * @return array<string, string>
     */
    public function columnLabels(): array;

    /**
     * @return class-string<Model>
     */
    public function modelClass(): string;

    public function bulkDeleteParameterName(): string;

    public function authorizeBulkDelete(User $user): bool;

    /**
     * @param  list<int>  $ids
     */
    public function bulkDelete(array $ids): int;

    /**
     * @return array<string, string>
     */
    public function redirectParameters(Request $request): array;
}
