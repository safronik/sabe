<?php

namespace Safronik\Controllers\Exceptions;

class ControllerException extends \Exception{
    
    /**
     * Machine to Machine info
     *
     * @var array|null
     */
    private ?array $m2m_message;

    public function __construct( string $message = "", int $code = 0, ?array $m2m_message = null )
    {
        $this->m2m_message = $m2m_message;

        parent::__construct( $message, $code );
    }
    
    /**
     * @return mixed
     */
    public function getM2mMessage()
    {
        return $this->m2m_message;
    }
    
    /**
     * @param mixed $m2m_message
     */
    public function setM2mMessage( array $m2m_message ): void
    {
        $this->m2m_message = $m2m_message;
    }
}