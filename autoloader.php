<?php

/**
 * Register extensions
 */
foreach( glob( __DIR__ . DIRECTORY_SEPARATOR . 'extension_*' ) as $extension ){
    require_once $extension;
}

/**
 * Autoload classes
 *
 * @param string $class
 *
 * @return void
 */
spl_autoload_register('loadClass' );

function loadClass( $classname ): void
{
    $class_filename = str_replace(
        '\\',
        DIRECTORY_SEPARATOR,
        __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . $classname . '.php'
    );

    if( ! str_contains( $classname, 'ReflectionHelper') && ! file_exists( $class_filename ) ){
        return;
    }
    
    require_once( $class_filename );
}