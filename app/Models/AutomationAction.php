<?php

namespace App\Models;

use App\Enums\AutomationActionType;
use Database\Factories\AutomationActionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'automation_id',
    'type',
    'config',
    'sort_order',
])]
class AutomationAction extends Model
{
    /** @use HasFactory<AutomationActionFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'type' => AutomationActionType::class,
            'config' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Automation, $this>
     */
    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }
}
