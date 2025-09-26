<?php

namespace Safronik\Controllers\Middlewares\Api\Auth;

use Safronik\Controllers\Exceptions\ControllerException;

class ApiKeyExtensionException extends ControllerException{

    public function __construct( string $message, int $code )
    {
        parent::__construct( $message, $code );
    }
}