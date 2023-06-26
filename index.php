<?php

require_once 'lib\autoloader.php';

$position = 'background: black; color: white;';
$position = explode( '; ', $position );
$position = array_map( fn( $rule ) => explode( ': ', trim( $rule, ';' ) ), $position );
$position = array_combine( array_column( $position, 0), array_column( $position, 1) );

try{
    new Safronik\Core\Core();
    new \Safronik\Core\Router(
        new \Safronik\Services\Request\Request()
    );
}catch(\Exception $exception){
    var_dump( $exception->getMessage() );
}