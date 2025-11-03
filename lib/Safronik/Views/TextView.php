<?php

namespace Safronik\Views;

use JsonException;
use Safronik\Views\Responses\ResponseInterface;

class TextView extends BaseView
{
    public const CONTENT_TYPE = 'text/plain';

    public function __construct()
    {
        header( 'Content-Type: ' . self::CONTENT_TYPE );
    }

    public function render(): static
    {
        echo $this->message;
        echo "\n";
        echo var_export($this->data , true );

        http_response_code( $this->response_code );

        return $this;
    }

    /**
     * @throws JsonException
     */
    public function renderResponse( ResponseInterface $response ): static
    {
        return $this
            ->setMessage( $response->message )
            ->setData( $response->data )
            ->setResponseCode( $response->code )
            ->render();
    }
}