<?php

namespace Safronik\Core;

use Safronik\Services\Cache\CacheOptions;
use Safronik\Services\Request\Request;
use Safronik\Services\Cache\Cache;

class Router
{
    public function __construct( private Request $request )
    {
        // @todo log visitor and request
        $cache = new Cache( new CacheOptions() );
        $cache->isMethodShouldBeCached( $this->request->method )
            && $cache->cache( $this->request );
        
        if( REST::isREST( $this->request ) ){
            new REST( $this->request->shiftRoute() );
        }
    }
}