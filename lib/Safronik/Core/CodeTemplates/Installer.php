<?php

namespace Safronik\Core\CodeTemplates;

trait Installer
{    
    /** Returns namespace from class name */
    public static function getNamespace( $string ): ?string
    {
        return pathinfo( $string, PATHINFO_DIRNAME );
    }
	
    public static function getScheme(): ?array
    {
        $scheme_classname = substr( static::class, 0, strrpos( static::class, "\\")) . '\\SQLScheme';
        
        return class_exists( $scheme_classname ) && property_exists( $scheme_classname, 'schemas')
            ? $scheme_classname::$schemas
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