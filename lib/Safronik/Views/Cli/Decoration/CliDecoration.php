<?php

namespace Safronik\Views\Cli\Decoration;

class CliDecoration
{
    private const START_DECORATE = "\033[";
    private const END_DECORATE   = "m";
    private const RESET_DECORATE = "\033[0m";

    private const DECORATE_COLOR = "38;2;";
    private const DECORATE_BACK  = "48;2;";

    public const DEFAULT_TEXT_COLOR = "255;255;255";
    public const DEFAULT_TEXT_STYLE = "0";
    public const DEFAULT_BACK_COLOR = "0;0;0";

    public static function decorate(string $text, ?string $text_color = null, ?string $back_color = null, ?int $style = null ): string
    {
        $decoration = [];
        $style       && $decoration[] = self::encodeStyle( $style );
        $text_color  && $decoration[] = self::DECORATE_COLOR . self::encodeColor( $text_color );
        $back_color  && $decoration[] = self::DECORATE_BACK  . self::encodeColor( $back_color );

        return
            self::START_DECORATE .
            implode( ';', $decoration ) .
            self::END_DECORATE .
            $text .
            self::RESET_DECORATE;
    }

    public static function encodeStyle( $style ): string
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
            default         => self::DEFAULT_TEXT_STYLE,
        };
    }

    public static function encodeColor($color ): string
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
}