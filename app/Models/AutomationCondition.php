<?php

namespace App\Models;

use App\Enums\AutomationConditionField;
use App\Enums\AutomationConditionOperator;
use Database\Factories\AutomationConditionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'automation_id',
    'field',
    'operator',
    'value',
    'sort_order',
])]
class AutomationCondition extends Model
{
    /** @use HasFactory<AutomationConditionFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'field' => AutomationConditionField::class,
            'operator' => AutomationConditionOperator::class,
            'value' => 'array',
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

    public function valueText(): string
    {
        $value = $this->value['text'] ?? null;

        return is_string($value) ? $value : '';
    }
}
