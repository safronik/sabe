<?php

namespace Safronik\Apps\Installer;

use Safronik\Services\DBStructureHandler\DBStructureHandler;
use Safronik\Services\Options\Options;
use Safronik\Services\DBStructureHandler\SQLScheme;
use Safronik\Core\Validator\Validator;
use Safronik\Core\CodeTemplates\Interfaces\Installable;
use Safronik\Core\CodeTemplates\Interfaces\Eventable;
use Safronik\Core\CodeTemplates\Event;

class Installer implements Eventable
{
    use Event;
    
    private Installable|string  $class;
    private ?DBStructureHandler $db_handler;
    private ?Options            $option_handler;
    
    private string $slug;
    private array  $options;
    private array  $scheme;
    
    public function __construct(
        Installable|string $class,
        ?DBStructureHandler $db_handler = null,
        ?Options $option_handler = null
    ){
        $this->class          = $class;
        $this->db_handler     = $db_handler;
        $this->option_handler = $option_handler;
        
        $this->validateDBStructure()
            && $this->scheme  = $this->class::getScheme();
            
        $this->validateOptions()
            && $this->slug    = $this->class::getSlug()
            && $this->options = $this->class::getOptions();
    }
    
    private function validateDBStructure()
    {
        ! ( Validator::init()->app( $this->class )->isInstallable() )
            && throw new \Exception( "App does not implements Installable interface." );
    
        ! ( Validator::init()->app( $this->class )->hasSQLScheme() && $this->db_handler )
            && throw new \Exception( "No SQL scheme provided by $this->class or no DB handler passed to constructor" );
        
        return true;
    }
    
    private function validateOptions()
    {
        ! ( Validator::init()->app( $this->class )->hasOptions() && $this->option_handler )
            || throw new \Exception( "No options passed by $this->class or no options handler passed to constructor" );
        
        return true;
    }
    
    public function _install(): bool
    {
        return ( isset( $this->scheme )               && $this->createDBStructure( $this->scheme ) ) ||
               ( isset( $this->options, $this->slug ) && $this->prepareOptions( $this->options, $this->slug ) );
    }
    
    public function update(): bool
    {
        return ( isset( $this->scheme )               && $this->updateDBStructure( $this->scheme ) ) ||
               ( isset( $this->options, $this->slug ) && $this->updateOptions( $this->options, $this->slug ) );
    }
    
    public function uninstall(): bool
    {
        return
            ( isset( $this->scheme )         && $this->deleteDBStructure( $this->scheme ) ) ||
            ( isset( $this->options, $this->slug ) && $this->deleteOptions( $this->options, $this->slug ) );
    }
    
    private function createDBStructure( array $scheme ): bool
    {
        return $this->db_handler
            ->setScheme( new SQLScheme( $scheme ) )
            ->fix();
    }

    private function deleteDBStructure( array $scheme ): bool
    {
        return $this->db_handler
            ->setScheme( new SQLScheme( $scheme ) )
            ->drop();
    }
    
    private function deleteOptions( array $options_names, string $slug ): bool
	{
        $out = true;
        $this->option_handler
            ->setOptionsGroup( $slug )
            ->setOptionsToLoad( $options_names )
            ->loadOptions();
        
		foreach( $options_names as $option_name ){
			$out = $out && $this->options->$option_name->remove();
		}
        
        return $out;
	}
    
    private function _prepareOptions( $options_names, $slug ): bool
    {
        $out = true;
         $this->option_handler
            ->setOptionsGroup( $slug )
            ->setOptionsToLoad( $options_names )
            ->loadOptions();
        
		foreach( $options_names as $option_name ){
			$out = $out && $this->options->$option_name->save();
		}
        
        return $out;
	}
    
    private function updateOptions( array $options, string $slug ): bool
    {
        return true;
    }
    
    private function updateDBStructure( array $scheme ): bool
    {
        return $this->createDBStructure( $scheme );
    }
}