<?php

namespace Safronik\Core;

use Safronik\Services\Request\Request;

class REST
{
    private Request $request;
    private static string $rest_external_route = 'rest';
    
    public function __construct( Request $request )
    {
        $this->request = $request;
    }
    
    public static function isREST( Request $request ): bool
    {
        return $request->currentRoute() === self::$rest_external_route;
    }
    
    /**
     * @return Request
     */
    private function getAvailableRoutes(): Request
    {
        return $this->request;
    }
}