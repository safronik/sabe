<?php

namespace Safronik\Controllers\Api;

use Safronik\Controllers\Controller;
use Safronik\Router\Routes\Route;
use Safronik\Views\Api\ApiViewFabric;
use Safronik\Views\JsonView;

abstract class ApiController extends Controller{

    public function __construct( Route $route )
    {
//        $this->view = ApiViewFabric::fabricBy( ApiViewFabric::ACCEPT_HEADER );
        $this->view = new JsonView();

        parent::__construct( $route );
    }
}