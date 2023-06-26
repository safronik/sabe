<?php

namespace Safronik\Apps\SiteStructure;

trait SABETrait
{
    public function getCSSString(): string
    {
        return $this->css;
    }
    
    public function getJS(): string
    {
        return $this->js;
    }
    
}