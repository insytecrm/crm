<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\LeadBudget;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateLeadBudgetRequest;
use App\Models\Lead;
use App\Support\LeadDrawerRedirect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LeadBudgetController extends Controller
{
    public function update(UpdateLeadBudgetRequest $request, Lead $lead): JsonResponse|RedirectResponse
    {
        $newBudget = $request->enum('budget', LeadBudget::class);
        $previousBudget = $lead->budget;

        if ($previousBudget === $newBudget) {
            if ($this->wantsJson($request)) {
                return response()->json($this->budgetPayload($newBudget));
            }

            return $request->boolean('redirect_to_listing')
                ? back()
                : LeadDrawerRedirect::to($lead);
        }

        $lead->update(['budget' => $newBudget]);

        if ($this->wantsJson($request)) {
            return response()->json([
                ...$this->budgetPayload($newBudget),
                'message' => __('Lead budget updated.'),
            ]);
        }

        if ($request->boolean('redirect_to_listing')) {
            return back()->with('status', __('Lead budget updated.'));
        }

        return LeadDrawerRedirect::to($lead, __('Lead budget updated.'));
    }

    /**
     * @return array{budget: ?string, label: string}
     */
    private function budgetPayload(?LeadBudget $budget): array
    {
        return [
            'budget' => $budget?->value,
            'label' => $budget?->label() ?? '—',
        ];
    }

    private function wantsJson(UpdateLeadBudgetRequest $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }
}
