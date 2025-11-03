<?php

namespace Safronik\Controllers\Exceptions;

class EndpointNotImplementedException extends ControllerException
{
    public function __construct( string $message = "", int $code = 0 )
    {
        parent::__construct( $message, $code );
    }
}