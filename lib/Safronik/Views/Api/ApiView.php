<?php

namespace Safronik\Views\Api;

use Safronik\CodePatterns\Structural\DI;
use Safronik\Controllers\Exceptions\ControllerException;
use Safronik\Globals\Header;
use Safronik\Models\Entities\EntityObject;
use Safronik\Views\JsonView;

class ApiView{
    
    private string $view_type;
    
    public function __construct( string $view_type = JsonView::class )
    {
        $this->view_type = match( true ){
            str_contains( Header::get('accept'), 'application/json') => JsonView::class,
            // str_contains( Header::get('accept'), 'application/xml')  => XmlView::class,
            default => $view_type,
        };

    }
    
    public function outputError( \Exception $exception ): void
    {
        $response = new ApiResponse();
        
        $response->setError( true);
        $response->setMessage( $exception->getMessage() );
        $exception instanceof ControllerException
            && $response->setM2mMessage( $exception->getM2mMessage() );
        
        DI::get( $this->view_type )->render( $response, $exception->getCode() );
    }
    
    public function outputSuccess( array|EntityObject $data, string $message = '' ): void
    {
        $response = new ApiResponse();
        
        $response->setMessage( $message);
        $response->setData( $data );
        
        DI::get( $this->view_type )->render( $response, 200 );
    }
}