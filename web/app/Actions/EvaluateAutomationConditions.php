<?php

namespace App\Actions;

use App\Enums\AutomationConditionField;
use App\Enums\AutomationConditionOperator;
use App\Enums\PropertyType;
use App\Models\Automation;
use App\Models\AutomationCondition;
use App\Models\Lead;
use App\Models\Property;
use BackedEnum;
use Illuminate\Support\Str;

class EvaluateAutomationConditions
{
    /**
     * @param  array<string, mixed>  $metadata
     * @return array{
     *     passed: bool,
     *     checks: list<array{field: string, operator: string, expected: string, actual: string, passed: bool}>
     * }
     */
    public function handle(Automation $automation, Lead $lead, array $metadata = []): array
    {
        $checks = [];

        foreach ($automation->conditions as $condition) {
            [$actual, $expected] = $this->comparisonValues($condition, $lead, $metadata);
            $passed = $this->matches($condition->operator, $actual, $expected);

            $checks[] = [
                'field' => $condition->field->label(),
                'operator' => $condition->operator->label(),
                'expected' => $expected,
                'actual' => $actual,
                'passed' => $passed,
            ];
        }

        return [
            'passed' => collect($checks)->every(fn (array $check): bool => $check['passed']),
            'checks' => $checks,
        ];
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array{0: string, 1: string}
     */
    private function comparisonValues(AutomationCondition $condition, Lead $lead, array $metadata): array
    {
        $actual = $this->actualValue($condition, $lead, $metadata);
        $expected = $condition->valueText();

        if (! $this->resolvesPropertyProject($condition, $expected)) {
            return [$actual, $expected];
        }

        $property = Property::query()->where('project_name', $expected)->first();

        if ($property === null) {
            return [$actual, $expected];
        }

        return [
            $this->scalar($lead->property_type).'|'.$this->scalar($lead->location),
            $this->scalar($property->property_type).'|'.$this->scalar($property->project_location),
        ];
    }

    private function resolvesPropertyProject(AutomationCondition $condition, string $expected): bool
    {
        if ($condition->field !== AutomationConditionField::PropertyType
            && $condition->field !== AutomationConditionField::PropertyProject) {
            return false;
        }

        if (! in_array($condition->operator, [
            AutomationConditionOperator::Equals,
            AutomationConditionOperator::NotEquals,
            AutomationConditionOperator::In,
        ], true)) {
            return false;
        }

        return PropertyType::tryFromMixed($expected) === null;
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function actualValue(AutomationCondition $condition, Lead $lead, array $metadata): string
    {
        if ($condition->operator === AutomationConditionOperator::ChangedTo) {
            $to = $metadata['to'] ?? null;

            if (is_string($to) && $to !== '') {
                return $to;
            }
        }

        $value = match ($condition->field) {
            AutomationConditionField::Status => $lead->status,
            AutomationConditionField::Source => $lead->source,
            AutomationConditionField::Budget => $lead->budget,
            AutomationConditionField::PropertyType, AutomationConditionField::PropertyProject => $lead->property_type,
            AutomationConditionField::Location => $lead->location,
            AutomationConditionField::SiteVisitOutcome => $metadata['outcome'] ?? $metadata['to'] ?? '',
        };

        return $this->scalar($value);
    }

    private function matches(AutomationConditionOperator $operator, string $actual, string $expected): bool
    {
        return match ($operator) {
            AutomationConditionOperator::Equals, AutomationConditionOperator::ChangedTo => $this->same($actual, $expected),
            AutomationConditionOperator::NotEquals => ! $this->same($actual, $expected),
            AutomationConditionOperator::Contains => $actual !== '' && Str::contains(Str::lower($actual), Str::lower($expected)),
            AutomationConditionOperator::IsEmpty => $actual === '',
            AutomationConditionOperator::In => collect(explode(',', $expected))
                ->map(fn (string $item): string => trim($item))
                ->filter()
                ->contains(fn (string $item): bool => $this->same($actual, $item)),
        };
    }

    private function same(string $actual, string $expected): bool
    {
        return Str::lower(trim($actual)) === Str::lower(trim($expected));
    }

    private function scalar(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }
}
