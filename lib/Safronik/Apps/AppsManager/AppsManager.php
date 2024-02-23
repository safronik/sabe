<?php

namespace Safronik\Apps\AppsManager;

use Safronik\Apps\App;

final class AppsManager extends App
{
    protected static string $slug = 'apps';
    protected static array $options_to_load = [ 'installed_apps', 'enabled_apps', 'system_apps' ];
    
    public function __construct( ...$params )
    {
        parent::__construct( ...$params );
        
        // $this->loadApps( $params['apps_to_load'] );
    }
    
    private function loadApps( $apps ): void
    {
        foreach( $apps as $alias => $data ){
            if( isset( $this->apps[ $alias ] ) ){
                continue;
            }
            
            if( ! class_exists( $data['classname'] ) ){
                throw new \Exception( "{$data['classname']} application not found." );
            }
            $this->apps[ $alias ] = new $data['classname'](
                $data['dependencies'],
            );
        }
    }
}