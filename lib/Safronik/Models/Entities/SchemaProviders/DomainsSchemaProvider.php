<?php

namespace Safronik\Models\Entities\SchemaProviders;

use Safronik\DBMigrator\Objects\Schemas;
use Safronik\Models\Entities\EntityObject;
use Safronik\Models\Entities\ValueObject;

/**
 * Recursively goes through every folder and gets all PHP-classes
 *  Separate ValueObjects from EntityObjects
 */
class DomainsSchemaProvider{
    
    // Input
    private string $entity_root_namespace;
    private array  $exclusions;
    
    // Process result
    private array  $entities = [];
    private array  $values;
    
    /**
     * @param string $domains_directory
     * @param string $entity_root_namespace
     * @param array  $exclusions
     */
    public function __construct( string $domains_directory, string $entity_root_namespace, array $exclusions = ['ValueObject', 'EntityObject', 'DummyEntity'] )
    {
        $this->entity_root_namespace = $entity_root_namespace;
        $this->exclusions            ??= $exclusions;
        
        $found    = [];
        $iterator = new \RecursiveDirectoryIterator($domains_directory );
        $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
        
        foreach( $iterator as $file ){
            if( $file->isFile() && $file->getExtension() === 'php' ){
                
                $file_basename = $file->getBasename('.php');
                if( $this->isExclusion( $file_basename) ){
                    continue;
                }
                
                $entity_namespace = $entity_root_namespace . $file->getPath() . '/' . $file->getBasename( '.php' );
                $entity_namespace = str_replace(
                    [ $domains_directory, '/' ],
                    [ '', '\\' ],
                    $entity_namespace
                );
                
                if( ! is_subclass_of( $entity_namespace, ValueObject::class ) ){
                    continue;
                }
                // var_dump( $file);
                
                $found[] = $entity_namespace;
                
                if( is_subclass_of( $entity_namespace, EntityObject::class ) ){
                    $this->entities[] = $entity_namespace;
                }
            }
            
        }
        
        $this->values = array_diff( $found, $this->entities );
    }
    
    public function getDomainsSchemas( array|string|object $entities = null ): Schemas
    {
        $entities ??= $this->entities;
        $entities = is_object( $entities ) ? [ $entities ] : (array) $entities;
        
        $schemas = [];
        
        // Entity tables
        foreach( $entities as $entity ){
            $schema_provider = new EntitySchemaProvider( $entity, $this->entity_root_namespace );
            $schemas[]       = $schema_provider->getEntitySchema();
        }
        
        // Object tables
        foreach( $entities as $entity ){
            $schema_provider = new EntitySchemaProvider( $entity, $this->entity_root_namespace );
            $schemas = array_merge( $schemas, $schema_provider->getObjectTablesSchemas() );
        }
        
        foreach( $entities as $entity ){
            $schema_provider = new EntitySchemaProvider( $entity, $this->entity_root_namespace );
            // Relations tables
            $schemas = array_merge( $schemas, $schema_provider->getRelationTablesSchemas() );
        }
        
        return new Schemas( $schemas );
    }
    
    private function isExclusion( $filename ): bool
    {
        return in_array( $filename, $this->exclusions, true );
    }
    
    public function getEntities(): array
    {
        return $this->entities;
    }
    
    public function getValues(): array
    {
        return $this->values;
    }
}