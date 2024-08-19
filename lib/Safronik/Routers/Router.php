<?php

namespace Safronik\Routers;

use Safronik\Routers\Exceptions\RouterException;

/**
 * Front controller
 */
class Router
{
    private Route  $route;
    private string $endpoint_type;
    private string $root_namespace;
    private array  $settings = [
        'behaviour' => 'rest_alternative',
    ];
    
    public function __construct( string $root_namespace = '', array $settings = [] )
    {
        $this->route = $this->newRoute(
            Request::getInstance()
        );
        $this->root_namespace = $root_namespace;
        $this->endpoint_type  = 'method';
        $this->settings       = array_merge( $this->settings, $settings );
    }
    
    /**
     * Route fabric
     *
     * @param Request $request
     *
     * @return Route
     */
    private function newRoute( Request $request ): Route
    {
        return new Route(
            $request->getType(),
            $request->getMethod(),
            $request->getPath(),
        );
    }
    
    public function findExecutables(): bool
    {
        switch( $this->settings['behaviour'] ?? 'rest_alternative' ){
            
            // Immediately set 404-controller if the called controller doesn't exist
            case 'strict':
                $this->ensureRouteAvailable( $this->route );
            
            // Reduce route every iteration until correct available controller will be found. Shows available endpoints
            case 'rest_strict':
                
                // @todo implement maybe
            
            // Once available route is met returns list of endpoints
            case 'rest_alternative':
                
                try{
                    $this->ensureRouteAvailable( $this->route );
                }catch( RouterException $exception ){
                    $this->route->reduceRoute();
                    $this->endpoint_type = 'action';
                    $this->ensureRouteAvailable( $this->route );
                }
                
                break;
        }
        
        return true;
    }
    
    private function ensureRouteAvailable( Route $route ): void
    {
        $this->isControllerAvailable( $route )
            || throw new RouterException('No controller available', 501 );
        
        // $this->isEndpointAvailable( $route )
        //     ||  throw new RouterException('No endpoint available', 501 );
    }
    
    public function isControllerAvailable( Route $route ): bool
    {
        return class_exists( $this->root_namespace . '\\' . $route->getPath() . 'Controller' );
    }
    
    public function isEndpointAvailable( Route $route ): bool
    {
        $controller = $this->root_namespace . '\\' . $route->getPath() . 'Controller';
        $method     = $this->endpoint_type . $route->getMethod();
        
        return method_exists( $controller, $method );
    }
    
    public function executeRoute(): void
    {
        $controller  = $this->root_namespace . '\\' . $this->route->getPath() . 'Controller';
        $method      = $this->route->getMethod();
        
        call_user_func([ new $controller( $this->route ), $this->endpoint_type . ucfirst($method ) ] );
    }
    
    public function setDefaultRoute(): true
    {
        $this->route = new Route(
            $this->route->getType(),
            Request::getInstance()->method,
            'Default',
        );
        
        return true;
    }
    
    private function getAvailableRotes()
    {
    
    }
}