<?php

namespace Safronik\Views;

class JsonView extends BaseView{
    
    public function init(): void
    {
        header( 'Content-Type: application/json' );
    }

    /**
     * @throws \JsonException
     */
    public function render(): ViewInterface
    {
        echo json_encode(
            [
                'data'=> $this->data,
                'message' => $this->message,
            ],
            JSON_THROW_ON_ERROR
        );
        http_response_code( $this->response_code );

        return $this;
    }

    public function renderError(\Exception $exception): ViewInterface
    {
        return $this
            ->setData( [ 'error' => $exception->getMessage() ] )
            ->setResponseCode( $exception->getCode() )
            ->render();
    }

    public function renderMessage(string $message): ViewInterface
    {
        return $this
            ->setMessage( $message )
            ->render();
    }

    public function renderData(object|array $data): ViewInterface
    {
        return $this
            ->setData( $data )
            ->render();
    }
}