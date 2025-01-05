<?php

require_once 'vendor/autoload.php';
require_once 'autoloader.php';

new Safronik\Core\Core( __DIR__ );

function App()
{
    return \Safronik\Core\Core::getInstance();
}

try{
    
    $router = new \Safronik\Router\Router( 'Controllers' );
    $router->findExecutable() && $router->executeRoute();
    
}catch(\Exception $exception){
    
    echo match( \Safronik\Router\Request::determineType() ){
        'cli' => "\033[91mError:\033[0m {$exception->getMessage()}\n",
        default => $exception->getMessage(),
    };
}