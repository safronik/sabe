<?php

namespace Safronik\Apps\Installer;

use Safronik\Core\CodeTemplates\Interfaces\Installable;

use Safronik\Services\DBStructureHandler\DBStructureHandler;
use Safronik\Services\Options\Options;
use Safronik\Services\DBStructureHandler\SQLScheme;
use Safronik\Core\Validator\Validator;

class Installer
{
    private Installable|string  $class;
    private ?DBStructureHandler $db_structure;
    private ?Options            $option_handler;
    
    private string $slug;
    private array  $options;
    private array  $scheme;
    
    /**
     * @throws \Exception
     */
    public function __construct(
        Installable|string $class,
        ?DBStructureHandler $db_handler = null,
        ?Options $option_handler = null
    ){
        $this->class          = $class;
        $this->db_structure   = $db_handler;
        $this->option_handler = $option_handler;
        
        ! ( Validator::app( $this->class )->isInstallable() )
            && throw new \Exception( 'App does not implements Installable interface' );
        
        $this->validateDBStructure()
            && $this->scheme  = $this->class::getScheme();
            
        $this->validateOptions()
            && ( $this->slug    = $this->class::getSlug() )
            && ( $this->options = $this->class::getOptions() );
    }
    
    private function validateDBStructure(): bool
    {
        \Safronik\Services\Event\Event::after( 'install', function(){} );
        return Validator::app( $this->class )->hasSQLScheme() && $this->db_structure;
    }
    
    private function validateOptions(): bool
    {
        return Validator::app( $this->class )->hasOptions() && $this->option_handler;
    }
    
    public function install(): bool
    {
        return
            ( isset( $this->scheme )               && $this->createDBStructure( $this->scheme ) ) ||
            ( isset( $this->options, $this->slug ) && $this->prepareOptions( $this->options, $this->slug ) );
    }
    
    public function update(): bool
    {
        return
            ( isset( $this->scheme )               && $this->updateDBStructure( $this->scheme ) ) ||
            ( isset( $this->options, $this->slug ) && $this->updateOptions( $this->options, $this->slug ) );
    }
    
    public function uninstall(): bool
    {
        return
            ( isset( $this->scheme )         && $this->deleteDBStructure( $this->scheme ) ) ||
            ( isset( $this->options, $this->slug ) && $this->deleteOptions( $this->options, $this->slug ) );
    }
    
    /**
     * @throws \Exception
     */
    private function createDBStructure( array $scheme ): bool
    {
        return $this->db_structure->updateSchema( new SQLScheme( $scheme ) );
    }
    
    /**
     * @throws \Exception
     */
    private function deleteDBStructure( array $scheme ): bool
    {
        return $this->db_structure->dropSchema( new SQLScheme( $scheme ) );
    }
    
    /**
     * @throws \Exception
     */
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
    
    /**
     * @throws \Exception
     */
    private function prepareOptions( $options_names, $slug ): bool
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
    
    /**
     * @throws \Exception
     */
    private function updateDBStructure( array $scheme ): bool
    {
        return $this->createDBStructure( $scheme );
    }
}