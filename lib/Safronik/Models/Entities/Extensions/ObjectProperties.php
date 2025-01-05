<?php

namespace Safronik\Models\Entities\Extensions;

/** @property $root_namespace */
trait ObjectProperties{
    
    private static string $_path;
    private static array  $_route;
    private static string $_table;
    
    /**
     * Get entity path from the entity root path
     *
     * @param string $entity_root_namespace
     *
     * @return string
     */
    public static function getPath( string $entity_root_namespace ): string
    {
        return static::$_path ?? str_replace( $entity_root_namespace . '\\', '', static::class );
    }
    
    /**
     * Breaks entity path into route
     *
     * @param string $entity_root_namespace
     *
     * @return array
     */
    public static function getRoute( string $entity_root_namespace ): array
    {
        return static::$_route
               ?? array_map(
                   static function( $val ){ return strtolower( $val ); },
                   explode( '\\', static::getPath( $entity_root_namespace ) )
               );
    }
    
    /**
     * Returns entity table name
     *
     * @param string|null $entity_root_namespace
     *
     * @return string
     */
    public static function getTable( ?string $entity_root_namespace = null ): string
    {
        return static::$_table
               ?? implode(
                   '_',
                   static::getRoute( $entity_root_namespace ?? static::$root_namespace ?? '' )
               );
    }
}