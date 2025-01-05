<?php

namespace Safronik\Core;

use Safronik\Models\Entities\Rule;
use Safronik\Helpers\ValidationHelper as BaseValidationHelper;

class ValidationHelper extends BaseValidationHelper
{
    public static function validate( array $data, array $rules ): void
    {
        self::convertRules( $rules );
        parent::validate( $data, $rules );
    }

    public static function validateRedundant( array $data, array $rules ): void
    {
        self::convertRules( $rules );
        parent::validateRedundant( $data, $rules );
    }

    public static function validateRequired( array $data, array $rules ): void
    {
        self::convertRules( $rules );
        parent::validateRequired( $data, $rules );
    }

    private static function convertRules( &$rules ): void
    {
        array_walk(
            $rules,
            static fn( array|Rule &$rule) => $rule = $rule instanceof Rule ? $rule->initial : $rule
        );
    }
}