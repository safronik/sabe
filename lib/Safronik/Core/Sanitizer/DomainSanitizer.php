<?php

namespace Safronik\Core\Sanitizer;

use Safronik\Services\DB\Schemas\SchemasProvider;

class DomainSanitizer
{
    public static function sanitize( &$data, ...$rule_set ): void
    {
        foreach( $rule_set as $rules ){
            foreach( $rules as $field => $rule ){
                self::setMissingOptionalToNull( $data, $field, $rule );
                self::setDefaultsToEmptyFields( $data, $field, $rule );
            }
        }
    }
    
    private static function setMissingOptionalToNull( &$data, $field, $rule ): void
    {
        $data[ $field ] = ! in_array( 'required', $rule, true ) && ! isset( $data[ $field ] )
            ? null
            : $data[ $field ];
    }
    
    private static function setDefaultsToEmptyFields( array &$data, string $field, $rule ): void
    {
        $data[ $field ] = ! isset( $data[ $field ] ) && isset( $rule['default'] )
            ? $rule['default']
            : $data[ $field ];
    }

}