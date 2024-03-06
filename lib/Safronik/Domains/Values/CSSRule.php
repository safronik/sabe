<?php

namespace Safronik\Domains\Values;

class CSSRule extends ValueObject{
    
    // Entity properties
    protected array $css;
    
    public function __construct( string $ccs_rule )
    {
        $rules = explode( ';', $css_string );
        array_walk( $rules, static function( &$value ){
            $value = explode( ':', $value, 1 );
        } );
        $rules = array_combine(
            array_column( $rules, 0 ),
            array_column( $rules, 1 )
        );
    }
    
    public function get( $rule )
    {
        return $this->css[ $rule ] ?? null;
    }
}