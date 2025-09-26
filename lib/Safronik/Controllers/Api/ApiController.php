<?php

namespace Safronik\Controllers\Api;

use Safronik\Controllers\Controller;
use Safronik\Router\Routes\AbstractRoute;
use Safronik\Views\Api\ApiView;
use Safronik\Views\ViewInterface;

abstract class ApiController extends Controller{

    protected ViewInterface $view;

    public function __construct( AbstractRoute $route, ApiView $view)
    {
        $this->view = $view;

        parent::__construct($route);
    }
}