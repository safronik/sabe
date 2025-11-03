<?php

namespace Safronik\Controllers\Cli;

use Safronik\Controllers\Controller;
use Safronik\Globals\Server;
use Safronik\Router\Endpoint;
use Safronik\Router\Routes\Route;
use Safronik\Views\Cli\CliView;
use Safronik\Views\ViewInterface;

abstract class CliController extends Controller{
    
    protected string $root;

    protected ViewInterface $view;

    public function __construct( Route $route, CliView $view)
    {
        $this->view = $view;

        parent::__construct($route);
    }

    protected function init(): void
    {
        $this->root = dirname( Server::get('argv')[0] );
    }
    
    public function methodHelp(): void
    {
        $endpoints_description = array_map(
            static fn( Endpoint $endpoint ) => $endpoint->compileHelp(),
            $this->getEndpoints()
        );

        $this->view
            ->setData($endpoints_description)
            ->render();
    }
}