<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TenantPermission;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesTeamRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('sales_teams', 'name')],
            'description' => ['nullable', 'string', 'max:5000'],
            'manager_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn (Builder $query) => $this->applyManagerConstraints($query))],
            'is_active' => ['sometimes', 'boolean'],
            'member_ids' => ['sometimes', 'array'],
            'member_ids.*' => ['integer', Rule::exists('users', 'id')->where(fn (Builder $query) => $this->applyMemberConstraints($query))],
        ];
    }

    /**
     * @return list<int>
     */
    public function memberIds(): array
    {
        $managerId = (int) $this->validated('manager_id');

        return collect($this->validated('member_ids', []))
            ->map(fn ($id): int => (int) $id)
            ->reject(fn (int $id): bool => $id === $managerId)
            ->unique()
            ->values()
            ->all();
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

    private function applyMemberConstraints(Builder $query): void
    {
        $query->where('is_active', true)
            ->whereNotIn('id', function (Builder $memberQuery): void {
                $memberQuery->select('user_id')->from('sales_team_user');
            });
    }
}
