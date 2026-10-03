<?php

namespace App\Casts;

use App\Enums\Concerns\ParsesFlexibleEnumValues;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @template T of ParsesFlexibleEnumValues
 */
class FlexibleEnumCast implements CastsAttributes
{
    /**
     * @param  class-string<T>  $enumClass
     */
    public function __construct(protected string $enumClass) {}

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $this->enumClass::tryFromMixed($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->enumClass::tryFromMixed($value)?->value;
    }
}
