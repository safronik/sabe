<?php

namespace Safronik\Services\DB\Gateways;

use Safronik\Core\CodeTemplates\Interfaces\Containerable;
use Safronik\Core\CodeTemplates\Container;
use Safronik\Services\DB\DB;

final class DBGateways implements Containerable
{
    use Container;
    
    //protected static string $interface_to_check = DBGatewayInterface::class;
    
    public function __construct( array $services = [], ?string $directory = null )
    {
        $directory  = $directory ?? __DIR__;
        $classnames = $this->getAvailableFromDirectory( $directory, 'DBGateway', true );
        
        $this->appendBulk($classnames);
    }
    
    /**
     * passing DB to parameters
     *
     * @param mixed $service
     * @param array $params
     *
     * @return void
     */
    protected function filterInitParameters( mixed $service, array &$params ): void
    {
        array_unshift( $params, DB::getInstance() );
    }
    
    private function getAvailableFromDirectory( $directory, $filter, $return_with_aliases = false ): array
    {
        $classes = [];
        $classes_with_aliases = [];
        $current_class = pathinfo( self::class, PATHINFO_FILENAME);
        foreach( glob( $directory . "/$filter*" ) as $gateway_path ){
            $classname = pathinfo( $gateway_path, PATHINFO_FILENAME);
            if( $current_class === $classname ){
                continue;
            }
            $alias = trim( strtolower( preg_replace('/[A-Z]{1}($|[0-9a-z])/', '.$0', str_replace( $filter, '', $classname ) ) ), '.');
            $classes_with_aliases[ $alias ] = __NAMESPACE__ . '\\' . $classname;
            $classes[] = __NAMESPACE__ . '\\' . $classname;
        }
        
        return $return_with_aliases ? $classes_with_aliases : $classes;
    }
}