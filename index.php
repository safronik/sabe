<?php

use Safronik\Core\Apps;

require_once 'vendor/autoload.php';
require_once 'autoloader.php';

const ROOT_DIR = __DIR__;

error_reporting( E_ALL );
ini_set( 'display_errors', true );

function config( string $app, string $request )
{
    return Apps::get( $app )->config->get( $request );
}

/**
 * Setup common things like:
 * - Application mode
 * - Error handling
 */
Apps::init( 'base' );
Apps::init( 'sabe' );

try{

    Apps::get( 'sabe' )->router->findExecutable()
        && Apps::get( 'sabe' )->router->executeRoute();

}catch(\Exception $exception){
    
    echo match( \Safronik\Router\Request::determineType() ){
        'cli' => "\033[91mError:\033[0m {$exception->getMessage()}\n",
        default => $exception->getMessage(),
    };
}