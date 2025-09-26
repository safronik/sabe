<?php

namespace Safronik\Controllers\Middlewares\Api\CallLimit\Exceptions;

use Safronik\Controllers\Exceptions\ControllerException;

class ApiCallLimitException extends ControllerException{

    public function __construct( string $message, int $code )
    {
        parent::__construct( $message, $code );
    }
}