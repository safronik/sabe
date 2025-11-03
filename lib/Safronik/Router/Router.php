<?php

namespace Safronik\Router;

use Safronik\CodePatterns\Exceptions\ContainerException;
use Safronik\CodePatterns\Structural\DI;
use Safronik\Controllers\Controller;
use Safronik\Core\Config\Config;
use Safronik\Router\Exceptions\RouterException;
use Safronik\Router\Routes\Route;

/**
 * Front controller
 *
 * Searches exact route and executes it
 */
class Router
{
    private Route $route;
    private DI    $di;

    public function __construct( Config $config, DI $di, Request $request, Route $route = null )
    {
        $this->di    = $di;
        $this->route = $route
            ?? Route::fabricRoute(
                $request->getType(),
                $request->getPath(),
                $config->get('app.namespace'),
                $request->getMethod()
            );
    }

    /**
     * @throws RouterException
     */
    public function findExecutable(): bool
    {
        try{
            $this->route->searchForAvailableEndpoint();
        }catch( RouterException ){
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
        $controller = $this->di->get(
            $this->route->getController(),
            [ 'route' => $this->route ]
        );

        try{
            $controller->executeEndpoint($this->route->getEndpoint());
        }catch ( \Exception $exception ){
            $controller->handleError( $exception );
        }
    }
}