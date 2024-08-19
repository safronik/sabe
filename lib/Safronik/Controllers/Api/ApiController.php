<?php

namespace Safronik\Controllers\Api;

use Safronik\Controllers\Controller;
use Safronik\Controllers\Extensions\ApiCallLimit\ApiCallLimitExtension;
use Safronik\Controllers\Extensions\AuthApiKey\AuthApiKeyExtension;

abstract class ApiController extends Controller{
    
    use AuthApiKeyExtension;
    use ApiCallLimitExtension;
    
    protected function init(): void
    {
    
    }
    
    public function getApiKey(): string
    {
        return '';
    }
}