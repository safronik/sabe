<?php

namespace Safronik\Controllers\Api;

use Safronik\Controllers\Controller;
use Safronik\Controllers\Extensions\ApiCallLimit\ApiCallLimitExtension;
use Safronik\Controllers\Extensions\AuthApiKey\AuthApiKeyExtension;
use Safronik\Router\Routes\AbstractRoute;
use Safronik\Views\Api\ApiView;
use Safronik\Views\ViewInterface;

abstract class ApiController extends Controller{
    
    use AuthApiKeyExtension;
    use ApiCallLimitExtension;

    protected ViewInterface $view;

    public function __construct( AbstractRoute $route, ApiView $view)
    {
        $this->view = $view;

        parent::__construct($route);
    }

    protected function getApiKey(): string
    {
        return '';
    }
}