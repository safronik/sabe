<?php

namespace Safronik\Domains\Values;

class InlineCSS extends ValueObject{
    
    // Entity properties
    protected array $css;
    
    public function __construct( string $css_string )
    {
        $this->css = $this->parseCSSString( $css_string );
    }
    
    public function getRule( $rule_name )
    {
        return $this->css[ $rule_name ] ?? null;
    }
    
    private function parseCSSString( string $css_string )
    {
        $rules = explode( ';', $css_string );
        array_walk( $rules, static function( &$value ){
            $value = explode( ':', $value, 2 );
            array_walk( $value, static function ( &$val ){
                $val = trim($val);
            } );
        } );
        
        return array_combine(
            array_column( $rules, 0 ),
            array_column( $rules, 1 )
        );
    }

}