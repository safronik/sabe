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
    $directory      = getSourceDirectoryByClassname( $classname );
    $class_filename = str_replace(
        '\\',
        DIRECTORY_SEPARATOR,
        $directory . DIRECTORY_SEPARATOR . $classname . '.php'
    );

    if( ! str_contains( $classname, 'ReflectionHelper') && ! file_exists( $class_filename ) ){
        return;
    }
    
    require_once( $class_filename );
}

function getSourceDirectoryByClassname( $classname )
{
    [ $root_namespace ] = explode( '\\', $classname );
    
    return match ( $root_namespace ) {
        'Controllers' => __DIR__ . DIRECTORY_SEPARATOR . 'App',
        'Models'      => __DIR__ . DIRECTORY_SEPARATOR . 'App',
        'Entities'    => __DIR__ . DIRECTORY_SEPARATOR . 'App',
        'Views'       => __DIR__ . DIRECTORY_SEPARATOR . 'App',
        default       => __DIR__ . DIRECTORY_SEPARATOR . 'lib',
    };
}