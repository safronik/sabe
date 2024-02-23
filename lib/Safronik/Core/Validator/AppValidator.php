<?php

namespace Safronik\Core\Validator;

use Safronik\Core\CodeTemplates\Interfaces\Installable;

class AppValidator
{
    public function __construct(
        private string|Installable $app
    ){}

    public function isInstallable(): bool
    {
        return Validator::init()
                        ->class( $this->app )
                        ->implements( Installable::class );
    }
    
    public function hasSQLScheme(): bool
    {
        return (bool) $this->app::getScheme();
    }
    
    public function hasOptions(): bool
    {
        return $this->app::getOptions() && $this->app::getSlug();
            
    }

}