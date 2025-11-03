<?php

namespace Controllers\Exceptions;

use Safronik\Controllers\Exceptions\ControllerException;

class ControllerNotImplementedException extends ControllerException
{
    public function __construct( string $message = "", int $code = 0 )
    {
        parent::__construct( $message, $code );
    }
}