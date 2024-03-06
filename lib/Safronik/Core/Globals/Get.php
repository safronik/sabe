<?php

namespace Safronik\Core\Globals;

class Get extends Variables{
	
	protected function getVariable( $name )
	{
		if ( function_exists('filter_input') ) {
            $value = filter_input(INPUT_GET, $name);
        }

        if ( empty($value) ) {
            $value = $_GET[ $name ] ?? '';
        }

        return $value;
	}
    
    protected function getAllVariablesNames(): array
    {
        return array_keys( $_GET );
    }

}