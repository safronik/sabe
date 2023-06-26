<?php

namespace Safronik\Core\Variables;

class Post extends Variables{
	
	protected function getVariable( $name )
	{
		if ( function_exists('filter_input') ) {
            $value = filter_input(INPUT_POST, $name);
        }

        if ( empty($value) ) {
            $value = $_POST[ $name ] ?? '';
        }

        return $value;
	}
    
    protected function getAllVariablesNames(): array
    {
        return array_keys( $_POST );
    }

}