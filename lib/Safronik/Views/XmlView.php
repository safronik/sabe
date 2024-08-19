<?php

namespace Safronik\Views;

class XmlView extends BaseView{
    
    public function init()
    {
        header( 'Content-Type: application/xml' );
    }
    
    public function setData( $data )
    {
        $this->data = $data;
    }
    
    public function render( $output, $http_response_code = 200 )
    {
        echo xmlrpc_encode( $output );
        http_response_code( $http_response_code );
        
        exit( 0 );
    }
    
}