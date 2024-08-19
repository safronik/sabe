<?php

namespace Safronik\Views\Cli;

class CliView{
    
    private const START_DECORATE = "\033[";
    private const END_DECORATE   = "m";
    private const RESET_DECORATE = "\033[0m";
    
    private const DECORATE_COLOR = "38;2;";
    private const DECORATE_BACK  = "48;2;";
    
    private string $text_color = "255;255;255";
    private string $back_color = "0;0;0";
    private string $style = "0";
    
    private function decorate( string $text, ?string $text_color = null, ?string $back_color = null, ?int $style = null ): string
    {
        $decoration = [];
        $style       && $decoration[] = $this->encodeStyle( $style );
        $text_color  && $decoration[] = self::DECORATE_COLOR . $this->encodeColorText( $text_color );
        $back_color  && $decoration[] = self::DECORATE_BACK  . $this->encodeColorBack( $back_color );
        
        $text =
            self::START_DECORATE .
                implode( ';', $decoration ) .
            self::END_DECORATE .
                $text .
            self::RESET_DECORATE;
        
        return $text;
    }
    
    private function encodeStyle( $style ): string
    {
        return match ( $style ) {
            'bold'          => 1,
            'pale'          => 2,
            'italic'        => 3,
            'cursive'       => 3,
            'underline'     => 4,
            'flash'         => 5,
            'striked'       => 6,
            'strikethrough' => 6,
            default         => $this->style,
        };
    }

    private function encodeColorText( ?string $color = null ): string
    {
        return $this->encodeColor( $color ?? $this->text_color );
    }

    private function encodeColorBack( ?string $color = null ): string
    {
        return $this->encodeColor( $color ?? $this->back_color );
    }
    
    private function encodeColor( $color ): string
    {
        return match ( $color ) {
            'red'    => '200;50;50',
            'green'  => '50;200;50',
            'blue'   => '50;50;200',
            'white'  => '255;255;255',
            'black'  => '0;0;0',
            default  => str_replace( [ ' ', ',', '.' ], ';', $color ),
        };
    }
    
    public function renderError( \Exception $exception ): void
    {
        $this->text_color = $this->encodeColor( 'red' );
        $this->render( (array) $exception );
        $this->text_color = $this->encodeColor( 'white' );
    }
    
    public function renderMessage( array|string $message, string $color = 'white' ): void
    {
        $this->text_color = $this->encodeColor( $color );
        $this->render( $message );
        $this->text_color = $this->encodeColor( 'white' );
    }
    
    public function render( array|string $data, string $prefix = '' ): void
    {
        if( is_array( $data  ) ){
            foreach( $data as $key => $datum ){
                echo $prefix . $this->decorate( $key, $this->text_color ) . ': ';
                if( is_array($datum ) ){
                    echo "\n";
                    $this->render( $datum, $prefix . "\t" );
                }else{
                     echo $datum . "\n";
                }
            }
        }
        
        if( is_scalar( $data ) ){
            echo $prefix . $this->decorate( $data, $this->text_color ) . "\n";
        }
    }
}