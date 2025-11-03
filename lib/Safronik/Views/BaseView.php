<?php

namespace Safronik\Views;

abstract class BaseView implements ViewInterface{

    protected array|object $data = [];
    protected string       $message = '';
    protected int          $response_code = 200;

    public function renderError( \Exception $exception ): static
    {
        return $this
            ->setMessage( $exception->getMessage() )
            ->setData( method_exists( $exception, 'getData')
                ? $exception->getData()
                : [] )
            ->setResponseCode( is_string( $exception->getCode() ) ? 500 : $exception->getCode() )
            ->render();
    }

    public function renderMessage( string $message ): static
    {
        return $this
            ->setMessage( $message )
            ->render();
    }

    public function renderData( array $data ): static
    {
        return $this
            ->setData( $data )
            ->render();
    }

    public function setData( mixed $data ): static
    {
        $this->data = $data;

        return $this;
    }

    public function setMessage( string $message ): static
    {
        $this->message = $message;

        return $this;
    }

    public function setResponseCode( int $response_code ): static
    {
        $this->response_code = $response_code;

        return $this;
    }
}