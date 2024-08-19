<?php

namespace Safronik\Core;

use Safronik\CodePatterns\Structural\DI;
use Safronik\DB\DBConfig;

/**
 * Class Core
 
 * @package Safronik\Core
 */
readonly class Core
{
    /**
     * Core constructor.
     */
    public function __construct( string $root_dir, array $additional_config = [] )
    {
        try{
            $this->configure(
                $root_dir,
                $additional_config
                    ?? [ 'dirs' => [
                        'root'    => $root_dir,
                        'runtime' => $root_dir . DIRECTORY_SEPARATOR . 'runtime',
                    ] ]
            );
            $this->setErrorReportingLevel();
            $this->setupDiContainer();
            $this->initializeModules();
        }catch( \Exception $exception ){
            throw $exception;
        }
    }
    
    private function configure( string $root_dir, array $additional_config ): void
    {
        Config::initialize(
            $root_dir . DIRECTORY_SEPARATOR . 'config',
            $additional_config
        );
    }
    
    private function setErrorReportingLevel(): void
    {
        switch( Config::get('options.mode') ){
            case 'production':
                error_reporting(0 );
                ini_set('display_errors', 'Off');
                break;
            case 'stage':
                // @todo implement
                break;
            case 'dev':
                error_reporting( E_ALL ^ E_DEPRECATED );
                ini_set( 'display_errors', 'On' );
                break;
            default:
                error_reporting( E_ALL ^ E_DEPRECATED );
                ini_set( 'display_errors', 'On' );
        }
    }
    
    private function setupDiContainer()
    {
        // $vendor_safronik_dir = $root_dir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'safronik';
        // $di_class_map = array_merge(
        //     DI::getClassMapForDirectory( $root_dir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Safronik', '\Safronik' ),
        //     DI::getClassMapForDirectory( $vendor_safronik_dir . DIRECTORY_SEPARATOR . 'db-wrapper' . DIRECTORY_SEPARATOR . 'src', '\Safronik\DB' ),
        //     DI::getClassMapForDirectory( $vendor_safronik_dir . DIRECTORY_SEPARATOR . 'db-migrator' . DIRECTORY_SEPARATOR . 'src', '\Safronik\DBMigrator' ),
        // );
        
        DI::initialize(
            Config::get('di.class_map'),
            Config::get('di.interface_map')
        );
        DI::setParametersFor(
            DBConfig::class,
            [ Config::get( 'db.mysql' ) ]
        );
    }
    
    /**
     * Initialize services
     */
    private function initializeModules(): void
    {
    
    }
}