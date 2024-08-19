<?php

namespace Safronik\Controllers;

use Safronik\Routers\Request;
use Safronik\Routers\Route;

abstract class Controller{
    
    protected Route   $route;
    protected Request $request;
    
    public function __construct( Route $route )
    {
        $this->request = Request::getInstance();
        $this->route   = $route;
        static::init();
    }
    
    abstract protected function init(): void;
    
    protected function getAvailableActions(): array
    {
        foreach( ( new \ReflectionClass( static::class ) )->getMethods() as $method ){
            if( $method->isPublic() && ( str_starts_with( $method->getName(), 'action' ) ) ){
                $routes[] = $method->getName();
            }
            
            if( $method->isPublic() && ( str_starts_with( $method->getName(), 'method' ) ) ){
                $routes[] = lcfirst( str_replace( 'method', '', $method->getName() ) );
            }
        }
        
        return $routes ?? [];
    }
}