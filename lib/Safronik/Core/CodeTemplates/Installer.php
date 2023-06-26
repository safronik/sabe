<?php

namespace Safronik\Core\CodeTemplates;

trait Installer
{
    public static function getScheme(): ?array
    {
        $scheme_classname = pathinfo( static::class, PATHINFO_DIRNAME ) . '\\SQLScheme';
        
        return class_exists( $scheme_classname ) && property_exists( $scheme_classname, 'scheme')
            ? $scheme_classname::$scheme
            : null;
    }
    
    public static function getSlug(): ?string
    {
        return static::$slug ?? null;
    }
    
    public static function getOptions(): ?array
    {
        return static::$options ?? null;
    }
}