<?php

namespace Safronik\Views;

abstract class BaseView{
    
    protected array $data;
    
    public function __construct()
    {
        $this->init();
    }
    
    abstract public function setData( $data );
    abstract public function render( $output, $http_response_code = 200 );
}