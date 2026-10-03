<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TenantPermission;
use App\Models\SalesTeam;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesTeamMemberRequest extends FormRequest
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
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function (Builder $query) use ($team): void {
                    $query->where('is_active', true)
                        ->where('id', '!=', $team->manager_id)
                        ->whereNotIn('id', function (Builder $memberQuery): void {
                            $memberQuery->select('user_id')->from('sales_team_user');
                        });
                }),
            ],
        ];
    }
}
