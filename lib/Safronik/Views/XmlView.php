<?php

namespace Safronik\Views;

class XmlView extends BaseView{

    public function __construct()
    {
        parent::__construct();

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
}