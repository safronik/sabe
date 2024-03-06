<?php

namespace Safronik\Core\Validator;

class DomainValidator
{
    
    /**
     * @throws \Exception
     */
    public static function validate( $data, ...$rule_set ): void
    {
        foreach( $rule_set as $rules ){
            
            self::validateRequired( $data, $rules );
            
            foreach( $data as $field => $value ){
                if( isset( $rules[ $field ] ) ){
                    ! empty( $rules[ $field ]['type'] )    && self::validateType(    $value, $field, $rules[ $field ]['type'] );
                    ! empty( $rules[ $field ]['content'] ) && self::validateContent( $value, $field, $rules[ $field ]['content'] );
                    // @todo validate content length
                }
            }
        }
    }
    
    private static function validateRequired( $data, array $rules ): void
    {
        foreach( $rules as $field => $rule ){
            in_array( 'required', $rule, true ) && ! isset( $data[ $field ] )
                && throw new \Exception("Field '$field' is missing");
        }
    }
    
    private static function validateType( $value, $field, $required_type ): void
    {
        gettype( $value ) !== $required_type &&
            throw new \Exception( "Field '$field' should be a {$required_type}, " . gettype( $value ) . ' given.');
    }
    
    private static function validateContent( $value, $field, $rule ): void
    {
        // Direct match. Set of variant
        is_array( $rule ) &&
            ! in_array( $value, $rule ) &&
            throw new \Exception("Field $field content '$value' should be one of the set (" . implode( ', ', $rule ) . ')' );
        
        // Regular expression match
        // @todo make isRegExp function somewhere somehow someday
        is_string( $rule ) &&
            preg_match( '@^[/#+\@%({\[<].+[/#+\@%)}\]>]$@', $rule ) &&
            ! preg_match( $rule, $value ) &&
            throw new \Exception( "Field $field content '$value' is not match pattern, " . $rule );
    }
}