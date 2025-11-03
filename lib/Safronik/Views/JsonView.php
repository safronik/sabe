<?php

namespace Safronik\Views;

use JsonException;
use Safronik\Views\Responses\ResponseInterface;

class JsonView extends BaseView{

    public function __construct()
    {
        header( 'Content-Type: application/json' );
    }

    /**
     * @throws JsonException
     */
    public function render(): static
    {
        echo json_encode(
            [
                'data'    => $this->data,
                'message' => $this->message,
            ],
            JSON_THROW_ON_ERROR
        );
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