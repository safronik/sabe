<?php

namespace Safronik\Core;

use Exception;
use Safronik\CodePatterns\Structural\DI;
use Safronik\Core\Config\Config;
use Safronik\Core\ErrorsProcessing\ErrorHandler;
use Safronik\Core\Exceptions\ConfigException;
use Safronik\DB\DBConfig;
use Safronik\Router\Router;

/**
 * Class Core
 
 * @package Safronik\Core
 */
readonly class Core
{
    private const DEFAULT_ROOT_DIR = DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'html';
    private const DIR_RUNTIME      = 'runtime';
    private const DIR_CONFIG       = 'config';
    private const DIR_CODE         = 'code';

    public Config $config;
    public DI     $di;
    public Router $router;

    /**
     * @throws ConfigException
     * @throws Exception
     */
    public function __construct( string $appName, array $additionalConfig = [] )
    {
        $rootNamespace    = $this->getAppRootNamespace( $appName );
        $rootDir          = $this->getAppRootDir( $appName );
        $additionalConfig = array_merge( $additionalConfig, [ 'app' => ['namespace' => $rootNamespace ] ] );

        $this->initAutoloader( $rootDir, $rootNamespace );
        $this->initConfig( $rootDir, $additionalConfig );
        $this->initErrorHandler();
        $this->initExceptionHandler();
        $this->initDiContainer();
        $this->initModules();
        $this->initRouter();
    }

    private function getAppRootNamespace( string $appName ): string
    {
        return ucfirst( $appName );
    }

    /**
     * @throws Exception
     */
    private function getAppRootDir( string $appName ): string
    {
        $appRootDir = defined( ROOT_DIR )
            ?               ROOT_DIR . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . $appName
            : self::DEFAULT_ROOT_DIR . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . $appName;

        is_dir( $appRootDir )
            || throw new Exception( 'App not found' );

        return $appRootDir;
    }

    /**
     * Register an autoloader function for the current application
     */
    private function initAutoloader( string $rootDir, string $rootNamespace ): void
    {
        spl_autoload_register(
            static function( $classname ) use ( $rootDir, $rootNamespace ): void{

                [ $classRootNamespace ] = explode( '\\', $classname );

                if( $classRootNamespace !== $rootNamespace ){
                    return;
                }

                $class_filename = str_replace(
                    ['\\', $rootNamespace],
                    [DIRECTORY_SEPARATOR, self::DIR_CODE],
                    $rootDir . DIRECTORY_SEPARATOR . $classname . '.php'
                );

                if( ! file_exists( $class_filename ) ){
                    return;
                }

                require_once( $class_filename );
            }
        );
    }

    /**
     * Initializes configuration
     * Uploads config into memory so it can be accessed by Apps::get('APP_NAME')->config->get('config.request.by.path')
     *
     * @param string $root_dir         Absolute path to the root directory (could be different for different apps)
     * @param array $additional_config Additional config to be merged with the default config
     *
     * @throws ConfigException
     */
    private function initConfig( string $root_dir, array $additional_config = [] ): void
    {
        $this->config = new Config(
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
     */
    private function initErrorHandler(): void
    {
        new ErrorHandler(
            $this->config->get( 'app.mode'),
            $this->config->get('settings.errors') ?? []
        );
    }

    private function initExceptionHandler(): void
    {

    }

    private function initDiContainer(): void
    {
        // Creating DI container
        $this->di = new DI(
            $this->config->get('di.class_map') ?? [],
            $this->config->get('di.interface_map') ?? []
        );

        // Setup common dependencies
        $this->di->setParametersFor(
            DBConfig::class,
            [ $this->config->get( 'db.mysql' ) ]
        );
    }

    /**
     * Initialize services
     */
    private function initModules(): void
    {

    }

    private function initRouter(): void
    {
        $this->router = $this->di->get(
            Router::class,
            [
                'config' => $this->config,
                'di'     => $this->di,
            ]
        );
    }
}