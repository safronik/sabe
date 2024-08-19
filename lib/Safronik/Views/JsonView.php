<?php

namespace Safronik\Views;

class JsonView extends BaseView{
    
    public function init()
    {
        header( 'Content-Type: application/json' );
    }
    
    public function setData( $data )
    {
        $this->data = $data;
    }
    
    public function render( $output, $http_response_code = 200 )
    {
        echo json_encode( $output, JSON_THROW_ON_ERROR );
        
        is_int( $http_response_code )
            ? http_response_code( $http_response_code )
            : http_response_code( '500' );
        
        exit( 0 );
    }
    
}