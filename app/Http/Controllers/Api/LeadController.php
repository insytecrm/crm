<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateLead;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLeadApiRequest;
use App\Http\Resources\Api\LeadResource;
use Illuminate\Http\JsonResponse;

class LeadController extends Controller
{
    public function store(StoreLeadApiRequest $request, CreateLead $createLead): JsonResponse
    {
        $lead = $createLead->handle($request->validated(), null);

        return (new LeadResource($lead))
            ->response()
            ->setStatusCode(201);
    }
}
