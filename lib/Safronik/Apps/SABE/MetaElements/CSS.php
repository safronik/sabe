<?php

namespace Safronik\Apps\SiteStructure\MetaElements;

use Safronik\Core\CodeTemplates\Hydrator;

class CSS
{
    use Hydrator;
    
    // Position
    public string $position = 'relative';
    public ?string $top;
    public ?string $bottom;
    public ?string $left;
    public ?string $right;
    public ?int    $z_index;
    
    // Size
    public ?string $width;
    public ?string $height;
    
    public function __construct( $css_string )
    {
        $this->hydrateFrom( $this->stringToArray( $css_string ) );
    }
    
    private function stringToArray( $css ): array
    {
        $css = explode( '; ', $css );
        $css = array_map( fn( $rule ) => explode( ': ', trim( $rule, ';' ) ), $css );
        $css = array_combine(
            array_column( $css, 0),
            array_column( $css, 1)
        );
        
        return $css;
    }
    
    public function arrayToString(): string
    {
        $out = [];
        foreach( $this as $style => $value ){
            $out[] = $style . ': ' . $value;
        }
        
        return implode( '; ', $out );
    }
    
    private function convertUnits( $units ): string
    {
        return match( $units ){
            'percents' => '%',
            default => 'px'
        };
    }
}