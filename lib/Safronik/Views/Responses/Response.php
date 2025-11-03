<?php

namespace Safronik\Views\Responses;

abstract class Response implements ResponseInterface{
    
    public bool   $error   = false;
    public string $message = self::DEFAULT_MESSAGE;
    public array  $data    = [];
    public int    $code    = self::DEFAULT_CODE;

    public static function makeByException( \Exception $exception, array $data = [] ): Response
    {
        $response = new static;
        $response->setError( $exception, $data );

        return $response;
    }

    public function setError( \Exception $exception, array $data = [] ): static
    {
        $this->error = true;
        $this->message = $exception->getMessage();
        $this->code    = $exception->getCode();
        $this->data    = $data;

        return $this;
    }
    
    public function setMessage( string $message ): static
    {
        $this->message = $message;

        return $this;
    }
    
    public function setData( array $data ): static
    {
        $this->data = $data;

        return $this;
    }
}