<?php

namespace App\Actions;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Property;
use App\Support\LeadSourcePath;

class CreateMicrositeEnquiry
{
    /**
     * @param  array{name?: ?string, phone?: ?string, email?: ?string, configuration?: ?string}  $data
     */
    public function handle(Property $property, array $data): Lead
    {
        $path = LeadSourcePath::fromSegments([
            [
                'key' => 'project',
                'id' => (string) $property->id,
                'label' => $property->project_name,
            ],
        ]);

        return Lead::query()->create([
            'name' => $data['name'] ?? __('Microsite enquiry'),
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'source' => LeadSource::Microsite->value,
            'sub_source' => $path['sub_source'],
            'source_context' => $path['source_context'],
            'location' => $property->project_location,
            'property_type' => $property->property_type,
            'configuration' => $data['configuration'] ?? null,
            'status' => LeadStatus::New,
            'last_activity_at' => now(),
        ]);
    }
}
