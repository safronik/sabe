<?php

namespace Extensions\ApiCallLimit\Exceptions;

use Safronik\Controllers\Exceptions\ControllerException;

class ApiCallLimitException extends ControllerException{
    
    /**
     * @param string $string
     * @param int    $int
     */
    public function __construct( string $message, int $code )
    {
        parent::__construct( $message, $code );
    }
}