<?php

namespace Safronik\Core;

use Safronik\CodePatterns\Generative\Singleton;
use Safronik\Core\Exceptions\ConfigException;
use Safronik\Helpers\ReflectionHelper;

class Config{
    
    use Singleton;
    
    private array $config = [];
    
    public function __construct( string $config_dir, array $additional_config = [] )
    {
        is_dir( $config_dir )
            || throw new ConfigException( "Invalid config dir given: $config_dir" );
        
        // Setup config from config files
        $this->getConfigsFromDirectory( $config_dir );
        
        // Append additional config passed directly. Overwrites other configs
        $this->config = $additional_config
            ? array_merge_recursive( $this->config, $additional_config )
            : $this->config;
    }
    
    private function getConfigsFromDirectory( string $config_dir ): void
    {
        foreach( glob( $config_dir . DIRECTORY_SEPARATOR . '*.php' ) as $file ){
            require_once $file;
            $this->config = isset( $config ) ? array_merge( $this->config, $config ) : $this->config;
            unset( $config );
        }
        
        ! empty( $this->config )
            || throw new ConfigException( "Config is empty for directory: $config_dir" );
    }
    
    /**
     * Access method
     *
     * @param string $request
     *
     * @return mixed
     * @throws ConfigException
     */
    public static function get( string $request ): mixed
    {
        self::isInitialized()
            || throw new ConfigException( 'Initialize config first' );
        
        return self::getInstance()->getConfig( $request );
    }

    /**
     * Walk through a config branch from leaf to root
     * Gets multiple config values
     * - from the initial request
     * - then reduce request keys one by one and append a given key to it until a stop key is faced
     *
     * Example:
     * - request: 'middleware.controllers.api.rest.user.get'
     * - key: 'common'
     * - until: 'middlewares'
     * Will return values for requests:
     *  - middlewares.controllers.api.rest.user.get
     *  - middlewares.controllers.api.rest.user.common
     *  - middlewares.controllers.api.rest.common
     *  - middlewares.controllers.api.common
     *  - middlewares.controllers.common
     *
     * @param string $request
     * @param string $keyToAppend
     * @param string|null $stopKey
     *
     * @return array
     *
     * @throws ConfigException
     */
    public static function getRegressiveWithKey( string $request, string $keyToAppend, string $stopKey = null  ): array
    {
        self::isInitialized()
            || throw new ConfigException( 'Initialize config first' );

        $result[] = (array) self::get( $request );

        $requestArray = explode( '.', $request );

        do{
            $currentRequest = implode( '.', $requestArray ) . ".$keyToAppend";
            $result[] = (array)self::get( $currentRequest );

            // Cut last part of request
            array_pop( $requestArray );

        }while( self::getLastRequestPart( $requestArray ) !== $stopKey );

        return array_filter( array_merge( ...$result ) );

    }

    public static function getLastRequestPart( array $request ): ?string
    {
        return array_pop( $request );
    }

    public static function export(): array
    {
        return self::getInstance()->config;
    }
    
    public function getConfig( string $request ): mixed
    {
        $config_route = explode( '.', $request );
        $base_path    = array_shift( $config_route );
        $config_value = $this->$base_path;
        
        foreach( $config_route as $path ){
            
            if( ! isset( $config_value ) ){
                return null;
            }
            
            $config_value = $config_value[ $path ] ?? null;
        }
        
        return $config_value;
    }
    
    public function setConfig( string $root ): void
    {
        $config = [
            'dir' => [
                'root'         => $root,
                'lib'          => $root . '/lib',
                'entities'     => $root . '/lib/Safronik/Models/Domains',
                'services'     => $root . '/lib/Safronik/Services',
                'controllers'  => $root . '/lib/Safronik/Controllers',
                'modules'      => $root . '/lib/Safronik/Core/Modules',
                'gateways'     => $root . '/lib/Safronik/Core/Modules/DB/Gateways',
                'repositories' => $root . '/lib/Safronik/Core/Modules/DB/Repositories',
            ],
            'namespaces' => [
                'entities'     => 'Safronik\Models\Domains',
                'services'     => 'Safronik\Services',
                'controllers'  => 'Safronik\Models\Domains',
                'modules'      => 'Safronik\Core\Modules',
                'gateways'     => 'Safronik\Core\Modules\DB\Gateways',
                'repositories' => 'Safronik\Core\Modules\DB\Repositories',
            ],
        ];
        
        $config['entities']     = $this->getAvailable(
            $config['dirs']['entities'],
            $config['namespaces']['entities'],
            '',
            static fn( $classes ) =>
                array_filter(
                    $classes,
                    static fn( $class ) => is_subclass_of( $class, \Safronik\Models\Entities\Entity::class)
                )
        );
        
        $config['services']     = $this->getAvailable(
            $config['dirs']['services'],
            $config['namespaces']['services'],
            'Service',
            'Safronik\Helpers\HelperReflection::filterFinalClasses'
        );
        
        $config['controllers']  = $this->getAvailable(
            $config['dirs']['controllers'],
            $config['namespaces']['controllers'],
            'Controller',
            'Safronik\Helpers\HelperReflection::filterFinalClasses'
        );
        $config['modules']      = $this->getAvailable(
            $config['dirs']['modules'],
            $config['namespaces']['modules'],
            'Module',
            'Safronik\Helpers\HelperReflection::filterFinalClasses'
        );
        $config['gateways']     = $this->getAvailable(
            $config['dirs']['gateways'],
            $config['namespaces']['gateways'],
            'Gateway',
            'Safronik\Helpers\HelperReflection::filterFinalClasses'
        );
        $config['repositories'] = $this->getAvailable(
            $config['dirs']['repositories'],
            $config['namespaces']['repositories'],
            'Repository',
            'Safronik\Helpers\HelperReflection::filterFinalClasses'
        );
        
        file_put_contents( $this->config_file,
            "<?php \n" . '$config = ' . var_export( $config, true ) . ";"
        );
    }
    
    private function getAvailable( string $directory, string $namespace, ?string $classname_contains = null, callable $filter_callback = null ): array
    {
        $classes = ReflectionHelper::getClassesFromDirectory(
            $directory,
            $namespace,
            filter         : $classname_contains,
            recursive      : true,
            filter_callback: $filter_callback
        );
        
        $aliases = array_map(
            static fn( $class ) => strtolower(
                str_replace(
                    $classname_contains,
                    '',
                    substr( $class, strrpos( $class, '\\' ) + 1 )
                )
            ),
            $classes,
        );
        
        return array_combine( $aliases, $classes );
    }
    
    public static function isDeveloperMode(): bool
    {
        return self::get('options.mode' ) === 'develop';
    }
    
    public function __get( string $name ): mixed
    {
        return $this->config[ $name ] ?? null;
    }
    
    public function __isset( string $name ): bool
    {
        return isset( $this->config[ $name ] );
    }
}