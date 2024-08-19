<?php

namespace Safronik\Views\Api\Rest;

use Safronik\Controllers\Exceptions\ControllerException;
use Safronik\Views\Api\ApiView;


class RestView extends ApiView{
    
    /**
     * Redefine to add available actions to error response
     *
     * @param \Exception $exception
     *
     * @return void
     */
    public function outputError( \Exception $exception, array $available_actions = [] ): void
    {
        // Not implemented
        if( $exception->getCode() === 501 && $available_actions ){
            
            $exception = new ControllerException(
                $exception->getMessage(),
                501,
                [ 'available_actions' => $available_actions ]
            );
        }
        
        parent::outputError( $exception );
    }
}