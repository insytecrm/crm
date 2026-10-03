<?php

namespace App\Http\Resources\Api;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Lead
 */
class LeadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'source' => $this->source,
            'sub_source' => $this->sub_source,
            'source_context' => $this->source_context,
            'budget' => $this->budget?->value,
            'location' => $this->location,
            'property_type' => $this->property_type?->value,
            'configuration' => $this->configuration,
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
