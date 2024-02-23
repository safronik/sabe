<?php

namespace Safronik\Apps;

use Safronik\Services\Services;
use Safronik\Services\Options\Options;

/**
 * Application controller. Uses Singleton.
 * It's the enter point for every part of the application.
 *
 * Holds settings, data and every other data of the plugin.
 * Loads modules depending on settings
 * Calls Dashboard to draw admin part.
 * Calls Exterior to draw public part.
 *
 * @version       0.1.0
 * @author        Roman Safronov
 * @license       GNU/GPL: https://www.gnu.org/licenses/gpl-2.0.html
 * @see1
 */
abstract class App
{
    protected static string $slug;
    protected array  $services = [];
    
    private array $options_to_load;
    
    public Options $options;
    
    public function __construct( $services = [], array $options = [] )
	{
        if( ! isset( static::$slug ) ){
            throw new \Exception( 'No slug defined for "' . static::class . '" application. Please, do so.' );
        }
        
        $this->options_to_load = $options;
        
        $this->setServices( $services );
	}
    
    private function setServices( $services ): void
    {
        foreach( $services as $service => $service_params ){
            if( is_int( $service ) ){
                $this->services[ $service_params ] = [];
                continue;
            }
            $this->services[ $service ] = $service_params;
        }
        
        if( static::$slug ){
            $this->services['options'] = [
                'options_to_load' => $this->options_to_load,
                'options_group'   => static::$slug
            ];
        }
        
        foreach( $this->services as $services_alias => $service_params ){
            $service           = Services::get( $services_alias, $service_params );
            $alias             = $service::getAlias();
            $this->$alias      = $service;
        }
    }
}