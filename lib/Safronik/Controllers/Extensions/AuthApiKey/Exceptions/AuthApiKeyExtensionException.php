<?php

namespace Safronik\Controllers\Extensions\AuthApiKey\Exceptions;

use Safronik\Controllers\Exceptions\ControllerException;

class AuthApiKeyExtensionException extends ControllerException{
    
    /**
     * @param string $string
     * @param int    $int
     */
    public function __construct( string $message, int $code )
    {
        parent::__construct( $message, $code );
    }
}