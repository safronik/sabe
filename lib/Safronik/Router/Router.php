<?php

namespace Safronik\Router;

use Safronik\CodePatterns\Exceptions\ContainerException;
use Safronik\CodePatterns\Structural\DI;
use Safronik\Controllers\Controller;
use Safronik\Router\Exceptions\RouterException;
use Safronik\Router\Routes\AbstractRoute;

/**
 * Front controller
 *
 * Searches exact route and executes it
 */
class Router
{
    private AbstractRoute $route;
    private array  $settings = [];
    
    public function __construct( string $root_namespace = '', array $settings = [] )
    {
        $request              = Request::getInstance();
        $this->settings       = array_merge( $this->settings, $settings );
        $this->route          = AbstractRoute::fabricRoute(
            $request->getType(),
            $request->getPath(),
            $root_namespace,
            $request->getMethod()
        );
    }

    public function findExecutable(): bool
    {
        try{
            $this->route->searchForAvailableEndpoint();
        }catch( RouterException $exception ){
            $this->route
                ->setDefault()
                ->ensureIsAvailable();
        }

        return true;
    }

    /**
     * @throws ContainerException
     */
    public function executeRoute(): void
    {
        /** @var Controller $controller */
        $controller = DI::get(
            $this->route->getController(),
            [ 'route' => $this->route ]
        );

        try{
            $controller->executeEndpoint($this->route->getEndpoint());
        }catch (\Exception $exception ){
            $controller->handleError( $exception );
        }
    }
}