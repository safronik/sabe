<?php

namespace Safronik\Core;

use Safronik\Helpers\SanitizerHelper as BaseSanitizerHelper;
use Safronik\Models\Entities\Rule;

class SanitizerHelper extends BaseSanitizerHelper
{
    /**
     * Sanitize data in different ways
     *
     * @param array $data
     * @param array $rules
     *
     * @return void
     */
    public static function sanitize( array &$data, array $rules ): void
    {
        self::convertRules( $rules );
        parent::sanitize( $data, $rules );
    }

    private static function convertRules( &$rules ): void
    {
        array_walk( $rules, static fn( array|Rule &$rule) => $rule = $rule instanceof Rule ? $rule->initial : $rule );
    }
}