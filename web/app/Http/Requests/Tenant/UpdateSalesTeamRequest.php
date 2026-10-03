<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TenantPermission;
use App\Models\SalesTeam;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalesTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(TenantPermission::TeamsManage) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var SalesTeam $team */
        $team = $this->route('team');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('sales_teams', 'name')->ignore($team)],
            'description' => ['nullable', 'string', 'max:5000'],
            'manager_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn (Builder $query) => $this->applyManagerConstraints($query))],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function isActive(): bool
    {
        return $this->boolean('is_active', true);
    }

    private function applyManagerConstraints(Builder $query): void
    {
        $query->where('is_active', true)
            ->whereIn('role_id', function (Builder $roleQuery): void {
                $roleQuery->select('id')
                    ->from('roles')
                    ->where('is_active', true)
                    ->whereIn('slug', ['manager', 'administrator']);
            });
    }
}
