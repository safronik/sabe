<?php

namespace Safronik\Models\Entities\SchemaProviders;

use Safronik\DBMigrator\Objects\Schema;
use Safronik\Models\Entities\Entity;
use Safronik\Models\Entities\SchemaProviders\MetaObjects\MetaEntity;
use Safronik\Models\Entities\Value;

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
    
    /**
     * Get entities from the directory
     *
     * @param string $domains_directory
     * @param string $entity_root_namespace
     * @param array  $exclusions
     */
    public function __construct( string $domains_directory, string $entity_root_namespace, array $exclusions = ['ValueObject', 'EntityObject', ] )
    {
        $this->entity_root_namespace = $entity_root_namespace;
        $this->exclusions            ??= $exclusions;

        $iterator = new \DirectoryIterator($domains_directory);
//        $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
        
        foreach( $iterator as $file ){
            if( $file->isFile() && $file->getExtension() === 'php' ){
                
                $file_basename = $file->getBasename('.php');
                if( $this->isExclusion( $file_basename) ){
                    continue;
                }
                
                $entity_class = $entity_root_namespace . $file->getPath() . '/' . $file->getBasename( '.php' );
                $entity_class = str_replace(
                    [ $domains_directory, '/' ],
                    [ '', '\\' ],
                    $entity_class
                );
                
                if( ! is_subclass_of( $entity_class, Value::class ) ){
                    continue;
                }
                
                if( is_subclass_of( $entity_class, Entity::class ) ){
                    $this->entities[] = $entity_class;
                }
            }
            
        }
    }
    
    public function getDomainsSchema( array|string|object $entities = null ): Schema
    {
        $entities ??= $this->entities;
        $entities = is_object( $entities ) ? [ $entities ] : (array) $entities;
        
        $schemas = [
            'entities'  => [],
            'values'    => [],
            'relations' => [],
        ];
        foreach( $entities as $entity ){
            
            $meta_entity     = new MetaEntity($entity, $this->entity_root_namespace);
            $schema_provider = new EntitySchemaProvider( $meta_entity );
            
            $schemas['entities'][] = $schema_provider->getEntitySchema();
            $schemas['values']     = array_merge($schemas['values'],    $schema_provider->getValuesSchema() );
            $schemas['relations']  = array_merge($schemas['relations'], $schema_provider->getRelationsSchema() );
        }
        
        // Entities should go first because other tables based on them
        $schemas = array_merge(
            $schemas['entities'] ?? [],
            $schemas['values'] ?? [],
            $schemas['relations'] ?? [],
        );
        
        return new Schema( $schemas );
    }
    
    public function getEntitiesTree(): array
    {
        return $tree;
    }
    
    private function isExclusion( $filename ): bool
    {
        return in_array( $filename, $this->exclusions, true );
    }
}