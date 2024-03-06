<?php

namespace Safronik\Core\Globals;

use Safronik\Core\CodeTemplates\Multiton;

abstract class Variables{
    
    use Multiton;
 
    protected array $variables;
    
	abstract protected function getVariable( $name );
	abstract protected function getAllVariablesNames();
 
	public static function get( $name ): string|array|int
    {
		$self  = static::getInstance();
        $value = $self->recall( $name ) ?? $self->getVariable( $name );
        
        isset( $value ) && $self->rememberVariable( $name, $value );
        
        return $value;
	}
	
    public static function getAllVariables(): array
    {
        $self = static::getInstance();
        
        foreach( $self->getAllVariablesNames() as $variable_name ){
            $self->variables[ $variable_name ] = $self->getVariable( $variable_name );
        }
        
        return $self->variables;
    }
    
	private function recall( $name ): array|string|int|null
    {
		return $this->variables[ static::class ][ $name ] ?? null;
	}
	
	private function rememberVariable( $name, $value ): void
    {
		$this->variables[ static::class ][ $name ] = $value;
	}
}