<?php

foreach( glob( __DIR__ . DIRECTORY_SEPARATOR . 'extension_*' ) as $extension ){
    require_once $extension;
}

/**
 *
 * @param string $class
 *
 * @return void
 */
spl_autoload_register(
    static function( $class ){
        // Register class auto loader
        //if( strpos( $class, 'SABE' ) !== false ){
            $class      = str_replace( '\\', DIRECTORY_SEPARATOR, $class );
            $class_file = __DIR__ . DIRECTORY_SEPARATOR . $class . '.php';
            if( file_exists( $class_file ) ){
                require_once( $class_file );
            }
        //}
    }
);
