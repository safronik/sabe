<?php

namespace Safronik\Core;

use Safronik\Services\Request\Request;
use Safronik\Services\Cache\Cache;
use Safronik\Apps\SABE\SABE;

class Router
{
    public function __construct( private Request $request )
    {
        // @todo log visitor and request
        if( Cache::isRequestMethodShouldBeCached( $this->request->method ) ){
            //new Cache( $this->request->id );
        }
        
        if( REST::isRESTRequest( $this->request ) ){
            new REST( $this->request->shiftRoute() );
        }
        
        new SABE(
            ...[
                'options'  => [ 'settings' ],
                'services' => [
                    'db',
                    'visitor',
                    'user',
                    'request',
                ],
            ]
        );
    }
}