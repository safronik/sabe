<?php

namespace Safronik\Core;

use Safronik\CodePatterns\Generative\Multiton;
use Safronik\CodePatterns\Structural\DI;
use Safronik\Core\Exceptions\ConfigException;
use Safronik\DB\DBConfig;

/**
 * Class Core
 
 * @package Safronik\Core
 */
readonly class Core
{
    // @todo uncomment when I decide to make multiapp
    // use Multiton;

    private const DIR_RUNTIME = 'runtime';
    private const DIR_CONFIG = 'runtime';

    /**
     * @throws ConfigException
     */
    public function __construct( string $root_dir, array $additional_config = [] )
    {
        $this->initConfig( $root_dir, $additional_config );
        $this->initErrorHandler();
        $this->initExceptionHandler();
        $this->initDiContainer();
        $this->initModules();
    }

    /**
     * Initializes configuration
     * Uploads config into memory so it can be accessed by Config::get('config.request.by.path')
     *
     * @param string $root_dir          Absolute path to the root directory (could be different for different apps)
     * @param array  $additional_config Additional config to be merged with the default config
     */
    private function initConfig( string $root_dir, array $additional_config = [] ): void
    {
        Config::initialize(
            $root_dir . DIRECTORY_SEPARATOR . self::DIR_CONFIG,
            array_merge(
                [
                    'dirs' => [
                        'root'    => $root_dir,
                        'runtime' => $root_dir . DIRECTORY_SEPARATOR . self::DIR_RUNTIME,
                    ],
                ],
                $additional_config
            )
        );
    }

    /**
     * Initializes error handling
     * Initializes error logger
     *
     * @return void
     *
     * @throws Exceptions\ConfigException
     *
     */
    private function initErrorHandler(): void
    {
        new ErrorHandler(
            Config::get('options.mode'),
            Config::get('options.error_handler_custom_params')
        );
    }

    private function initExceptionHandler(): void
    {

    }

    private function initDiContainer(): void
    {
//         $vendor_safronik_dir = $root_dir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'safronik';
//         $di_class_map = array_merge(
//             DI::getClassMapForDirectory( $root_dir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Safronik', '\Safronik' ),
//             DI::getClassMapForDirectory( $vendor_safronik_dir . DIRECTORY_SEPARATOR . 'db-wrapper' . DIRECTORY_SEPARATOR . 'src', '\Safronik\DB' ),
//             DI::getClassMapForDirectory( $vendor_safronik_dir . DIRECTORY_SEPARATOR . 'db-migrator' . DIRECTORY_SEPARATOR . 'src', '\Safronik\DBMigrator' ),
//         );

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
    private function initModules(): void
    {

    }
}