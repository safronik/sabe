<?php

namespace Safronik\Services\DB\Schemas;

class SchemasProvider
{
    private static string $schemas_namespace = '\Safronik\Services\DB\Schemas\\';
    
    /**
     * Provide table name for the entity
     *
     * @param string $entity
     *
     * @return string
     * @throws \Exception
     */
    public static function getEntityTable( string $entity ): string
    {
        /** @var SchemaAbstract $entity_schema_classname */
        $entity_schema_classname = self::getSchemaClassnameByEntity( $entity );
        
        return $entity_schema_classname::getTable();
    }
    
    public static function getEntitySchema( string $entity ): array
    {
        /** @var SchemaAbstract $entity_schema_classname */
        $entity_schema_classname = self::getSchemaClassnameByEntity( $entity );
        
        return $entity_schema_classname::getSchema();
    }
    
    public static function getEntityColumns( string $entity ): array
    {
        /** @var SchemaAbstract $entity_schema_classname */
        $entity_schema_classname = self::getSchemaClassnameByEntity( $entity );
        
        return $entity_schema_classname::getColumns();
    }
    
    /**
     * Provides schema classname for the entity
     * Checks if the schema exists
     *
     * @param string $entity
     *
     * @return string
     * @throws \Exception
     */
    private static function getSchemaClassnameByEntity( string $entity ): string
    {
        $entity                  = is_object( $entity ) ? $entity::class : $entity;
        $entity_name             = explode( '\\', $entity );
        $entity_schema_classname = self::$schemas_namespace . $entity_name[ count( $entity_name ) - 1 ];
        
        ! class_exists( $entity_schema_classname )
            && throw new \Exception('NoSchemaFound for entity: ' . $entity );
        
        return $entity_schema_classname;
    }
}