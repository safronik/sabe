<?php

namespace Safronik\Views;

class XmlView extends BaseView{
    
    public function init(): void
    {
        header( 'Content-Type: application/xml' );
    }

    public function render(): ViewInterface
    {
        echo xmlrpc_encode([
            'data'=> $this->data,
            'message' => $this->message,
        ]);
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
            ->setData( ['message' => $message ] )
            ->render();
    }

    public function renderData(object|array $data): ViewInterface
    {
        return $this
            ->setData( [ 'data' => $data ] )
            ->render();
    }
}