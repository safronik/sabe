<?php

namespace Safronik\Views\Api\Rest;

use Safronik\Controllers\Exceptions\ControllerException;
use Safronik\Views\Api\ApiView;
use Safronik\Views\ViewInterface;

class RestView extends ApiView{

    /**
     * Redefine to add available actions to error response
     *
     * @param array $available_actions
     *
     * @param \Exception $exception
     * @return ViewInterface
     */
    public function renderError( \Exception $exception, array $available_actions = [] ): ViewInterface
    {
        // Not implemented
        if( $exception->getCode() === 501 ){
            $exception = new ControllerException(
                $exception->getMessage(),
                501,
                [ 'available_actions' => $available_actions ]
            );
        }
        
        return parent::renderError( $exception );
    }
}