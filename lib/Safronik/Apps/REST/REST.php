<?php

namespace Safronik\Apps\REST;

use Safronik\Services\Request\Request;

class REST
{
    private Request $request;
    private static $rest_external_route = 'rest';
    
    public function __construct( Request $request )
    {
        $this->request = $request;
    }
    
    public static function isRESTRequest( Request $request ): bool
    {
        return $request->currentRoute() === self::$rest_external_route;
    }
    
    /**
     * @return Request
     */
    private function getAvailableRoutes()
    {
        return $this->request;
    }
}