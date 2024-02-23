<?php

namespace Safronik\Services;

use Safronik\Core\CodeTemplates\Container;
use Safronik\Core\CodeTemplates\Interfaces\Containerable;
use Safronik\Core\CodeTemplates\Interfaces\Installable;
use Safronik\Core\CodeTemplates\Interfaces\Serviceable;

final class Services implements Containerable
{
    use Container;
    
    protected static string $interface_to_check = Serviceable::class;
    
    private array         $installable;
    private Containerable $gateway_container;
    
    public function __construct( Containerable $gateway_container, ?array $services = []  )
    {
        $available               = $this->getAvaliableFromFolder( directory: __DIR__, return_with_aliases: true );
        $available               = $this->filterClassesByInterface( $available, self::$interface_to_check );
        $this->installable       = $this->filterClassesByInterface( $available, Installable::class );
        $this->gateway_container = $gateway_container;
        
        $this->appendBulk( array_merge( $available, $services ) );
    }
    
    public static function initialize( ...$params ): void
    {
        self::getInstance( ...$params );
    }
    
    public function getInstallable(): array
    {
        return $this->installable;
    }
    
    /**
     *
     *
     * @param mixed $service
     * @param array $params
     *
     * @return void
     */
    protected function filterInitParameters( mixed $service, array &$params ): void
    {
        // Get gateway if set
        $gateway_alias = $service::getGatewayAlias();
        if( $gateway_alias ){
            $gateway = $this->gateway_container::get( $gateway_alias );
            array_unshift( $params, $gateway );
        }
    }
    
    private function getAvaliableFromFolder( $directory, string $filter = null, $return_with_aliases = false )
    {
        $classes            = [];
        $classes_by_aliases = [];
        
        foreach( glob( $directory . "/*" ) as $item_path ){
            if( is_dir( $item_path ) ){
                $classname = pathinfo( $item_path, PATHINFO_FILENAME );
                $classname = __NAMESPACE__ . '\\' . $classname . '\\' . $classname;
                $alias     = $classname::getAlias() ?? trim( strtolower( preg_replace( '/[A-Z]{1}($|[0-9a-z])/', '.$0', str_replace( $filter, '', $classname ) ) ), '.' );
                $classes[] = $classname;
                $classes_by_aliases[ $alias ] = $classname;
            }
        }
    
        return $return_with_aliases ? $classes_by_aliases : $classes;
    }
}