<?php

namespace Safronik\Views\Api;

use Safronik\Globals\Header;
use Safronik\Views\HtmlView;
use Safronik\Views\TextView;
use Safronik\Views\ViewInterface;
use Safronik\Views\JsonView;
use Safronik\Views\XmlView;

abstract class ApiViewFabric{

    public const ACCEPT_HEADER = 'accept_type';

    public static function fabricBy( string $type, array $specification = [] ): ViewInterface
    {
        return match ( $type ) {
            static::ACCEPT_HEADER => static::fabricByAccept( $specification ),
            default => new JsonView,
        };
    }

    public static function fabricByAccept( array $specification = [] ): ViewInterface
    {
        $accept = Header::get( 'accept' );
        $accept = explode( ',', $accept );

        return match ( $accept[0] ?: false ) {
            'text/plain'       => new TextView,
            'text/html'        => new HtmlView,
            'application/json' => new JsonView,
            'application/xml'  => new XmlView,
            default => new TextView,
        };
    }
}