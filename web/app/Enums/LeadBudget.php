<?php

namespace App\Enums;

use App\Enums\Concerns\ParsesFlexibleEnumValues;

enum LeadBudget: string
{
    use ParsesFlexibleEnumValues;

    case BelowFiftyLakh = 'below_50_lakh';
    case FiftyLakhToSeventyLakh = '50_lakh_to_70_lakh';
    case SeventyLakhToNinetyLakh = '70_lakh_to_90_lakh';
    case NinetyLakhToOnePointTwoCrore = '90_lakh_to_1_2_crore';
    case OnePointTwoCroreToOnePointFiveCrore = '1_2_crore_to_1_5_crore';
    case OnePointFiveCroreToTwoCrore = '1_5_crore_to_2_crore';
    case TwoCroreToThreeCrore = '2_crore_to_3_crore';
    case ThreeCroreToFiveCrore = '3_crore_to_5_crore';
    case AboveFiveCrore = 'above_5_crore';

    public function label(): string
    {
        return match ($this) {
            self::BelowFiftyLakh => __('Below ₹50 Lakh'),
            self::FiftyLakhToSeventyLakh => __('₹50 Lakh to ₹70 Lakh'),
            self::SeventyLakhToNinetyLakh => __('₹70 Lakh to ₹90 Lakh'),
            self::NinetyLakhToOnePointTwoCrore => __('₹90 Lakh to ₹1.2 Crore'),
            self::OnePointTwoCroreToOnePointFiveCrore => __('₹1.2 Crore to ₹1.5 Crore'),
            self::OnePointFiveCroreToTwoCrore => __('₹1.5 Crore to ₹2 Crore'),
            self::TwoCroreToThreeCrore => __('₹2 Crore to ₹3 Crore'),
            self::ThreeCroreToFiveCrore => __('₹3 Crore to ₹5 Crore'),
            self::AboveFiveCrore => __('Above ₹5 Crore'),
        };
    }

    public static function fromAmount(?int $amount): ?static
    {
        if ($amount === null) {
            return null;
        }

        return match (true) {
            $amount < 5_000_000 => self::BelowFiftyLakh,
            $amount < 7_000_000 => self::FiftyLakhToSeventyLakh,
            $amount < 9_000_000 => self::SeventyLakhToNinetyLakh,
            $amount < 12_000_000 => self::NinetyLakhToOnePointTwoCrore,
            $amount < 15_000_000 => self::OnePointTwoCroreToOnePointFiveCrore,
            $amount < 20_000_000 => self::OnePointFiveCroreToTwoCrore,
            $amount < 30_000_000 => self::TwoCroreToThreeCrore,
            $amount < 50_000_000 => self::ThreeCroreToFiveCrore,
            default => self::AboveFiveCrore,
        };
    }
}
