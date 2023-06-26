<?php

namespace Safronik\Core\Variables;

class Server extends Variables{
    
    protected function getVariable( $name )
	{
		if ( function_exists('filter_input') ) {
            $value = filter_input(INPUT_SERVER, $name);
        }

        if ( empty($value) ) {
            $value = $_SERVER[ $name ] ?? '';
        }

        return $value;
	}
    
    protected function getAllVariablesNames(): array
    {
        return array_keys( $_SERVER );
    }
}