<?php

namespace Safronik\Router\Exceptions;

class RouterException extends \Exception{
    
    public function __construct( string $message = "", int $code = 0, ?\Throwable $previous = null )
    {
        parent::__construct(
            $message,
            $code,
            $previous
        );
    }
}