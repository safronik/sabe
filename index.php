<?php

require_once 'vendor/autoload.php';
require_once 'autoloader.php';

new Safronik\Core\Core( __DIR__ );

try{
    
    $router = new \Safronik\Routers\Router( 'Controllers' );
    $router->findExecutables() && $router->executeRoute();
    
}catch(\Exception $exception){
    
    echo match( \Safronik\Routers\Request::determineType() ){
        'cli' => "\033[91mError:\033[0m {$exception->getMessage()}\n",
        default => $exception->getMessage(),
    };
}