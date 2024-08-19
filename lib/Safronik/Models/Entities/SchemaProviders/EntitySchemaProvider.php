<?php

namespace Safronik\Models\Entities\SchemaProviders;

use Safronik\DBMigrator\Objects\Column;
use Safronik\DBMigrator\Objects\Constraint;
use Safronik\DBMigrator\Objects\Index;
use Safronik\DBMigrator\Objects\Table;
use Safronik\Models\Entities\EntityObject;
use Safronik\Models\Entities\ValueObject;

class EntitySchemaProvider
{
    
    private string $entity_root_namespace;
    private string $entity_slug;
    private array  $entity_route;
    private bool   $objects_as_json = false;
    private int    $default_object_length = 2048;
    private int    $default_string_length = 64;
    private int    $default_integer_length = 11;
    private ValueObject|string $entity_classname;
    
    public function __construct( string|object $entity, string $entity_root_namespace = 'Models\Entities' )
    {
        $this->entity_root_namespace = $entity_root_namespace;
        $this->entity_slug           = $this->getEntitySlug( $entity );
        $this->entity_classname      = is_object( $entity ) ? $entity::class : $entity;
        $this->entity_route          = $this->getEntityRoute( $this->entity_slug );
    }
    
    /**
     * Get entity path from the entity root path
     *
     * @param string|object $entity
     *
     * @return string
     * @throws \Exception
     */
    private function getEntitySlug( string|object $entity ): string
    {
        $entity_classname = is_object( $entity )
            ? $entity::class
            : $entity;
        
        class_exists( $entity_classname )
            || throw new \Exception( "Entity: '$entity_classname' is missing " );
        
        return str_replace( $this->entity_root_namespace. '\\', '', $entity_classname );
    }
    
    /**
     * Breaks entity path into route
     *
     * @param string|null $entity_slug
     *
     * @return array
     */
    private function getEntityRoute( string $entity_slug = null ): array
    {
        $entity_slug = $entity_slug ?? $this->entity_slug;
        
        return array_map(
            static function( $val ){ return strtolower( $val ); },
            explode( '\\', $entity_slug )
        );
    }
    
    /**
     * Returns entity table name
     *
     * @param $entity_route
     *
     * @return string
     */
    public function getEntityTable( $entity_route = null ): string
    {
        $entity_route = $entity_route ?? $this->entity_route;
        
        return implode( '_', $entity_route );
    }
    
    /**
     * Returns entity table name
     *
     * @param $entity_route
     *
     * @return string
     */
    public function getEntityRelationTableWith( $sub_entity ): string
    {
        return $this->getEntityTable() . '__' . ( new EntitySchemaProvider( $sub_entity ) )->getEntityTable();
    }

    
    /**
     * Returns entity table schema without secondary tables
     *
     * @return Table
     * @throws \Exception
     */
    public function getEntitySchema(): Table
    {
        return new Table(
            $this->getEntityTable(),
            $this->compileColumnsFromRules( $this->entity_classname::$rules ),
            $this->getEntityIndexes()
        );
    }
    
    /**
     * Get indexes for the entity
     *
     * @return Index[]
     * @throws \Exception
     */
    public function getEntityIndexes(): array
    {
        $indexes = [];
        if( isset( $this->entity_classname::$rules['id'] ) ){
            $indexes[] = new Index( [ 'key_name' => 'PRIMARY', 'columns' => [ 'id' ], 'comment' => 'Primary ID' ] );
        }
        
        return $indexes;
    }
    
    /**
     * Creates secondary tables to support the structure
     *
     * @return Table[]
     * @throws \Exception
     */
    public function getRelationTablesSchemas(): array
    {
        $schemas = [];
        foreach( $this->entity_classname::$rules as $rule ){
            
            if( $this->entity_classname::ruleIsSubEntity( $rule ) ){
                
                $entity_table        = $this->getEntityTable();
                $entity_table_column = $entity_table . '_id';
                
                $sub_entity_table        = $this->getEntityTable( $this->getEntityRoute( $this->getEntitySlug( $rule['type'] ) ) ); // todo refactor
                $sub_entity_table_column = $sub_entity_table . '_id';
                
                $schemas[] = new Table(
                    $entity_table . '__' . $sub_entity_table,
                    [
                        new Column( ['field' => $entity_table_column,     'type' => $this->entity_classname::$rules['id']['type'], 'null' => 'NO' ], ),
                        new Column( ['field' => $sub_entity_table_column, 'type' => $rule['type']::$rules['id']['type'],           'null' => 'NO' ], ),
                    ],
                    [
                        new Index( [
                            'key_name' => $entity_table_column,
                            'columns'  => [ $entity_table_column, $sub_entity_table_column, ],
                            'unique'   => true,
                        ] ),
                    ],
                    [
                        new Constraint([
                            'name'             => "FK_$entity_table" . '_' . $sub_entity_table,
                            'column'           => $entity_table_column,
                            'reference_table'  => $entity_table,
                            'reference_column' => 'id',
                        ]),
                        new Constraint( [
                            'name'             => "FK_$sub_entity_table" . '_' . $entity_table,
                            'column'           => $sub_entity_table_column,
                            'reference_table'  => $sub_entity_table,
                            'reference_column' => 'id',
                        ]),
                    ],
                );
            }
        }
        
        return $schemas;
    }
    
    /**
     * Returns schema for value objects of the entity
     *
     * @return Table[]
     * @throws \Exception
     */
    public function getObjectTablesSchemas(): array
    {
        $schemas = [];
        foreach( $this->entity_classname::$rules as $rule ){
            
            if( $this->entity_classname::ruleIsSubObject( $rule ) ){
                
                $entity_table        = $this->getEntityTable();
                $entity_table_column = $entity_table . '_id';
                
                $sub_object_class        = $rule['type'];
                $sub_entity_table        = $this->getEntityTable( $this->getEntityRoute( $this->getEntitySlug( $sub_object_class ) ) ); // todo refactor
                $sub_entity_table_column = $sub_entity_table . '_id';
                
                $parent_entity_id_rule = $this->entity_classname::$rules['id'];
                $parent_entity_id_rule['field'] = $entity_table_column;
                
                $schemas[] = new Table(
                    $entity_table . '__' . $sub_entity_table,
                    $this->compileColumnsFromRules( $sub_object_class::$rules, $parent_entity_id_rule ),
                    [
                        new Index( [
                            'key_name' => $entity_table_column,
                            'columns'  => [ $entity_table_column, ],
                            'unique'   => true,
                        ] ),
                    ],
                    [
                        new Constraint([
                            'name'             => "FK_$entity_table",
                            'column'           => $entity_table_column,
                            'reference_table'  => $entity_table,
                            'reference_column' => 'id',
                        ]),
                    ],
                );
            }
        }
        
        return $schemas;
    }
    
    /**
     * Returns entity columns SQL-scheme
     *
     * @return Column[]
     * @throws \Exception
     */
    private function compileColumnsFromRules( array $rules, ...$additional_rules )
    {
        $columns = [];
        $rules   = array_merge( $rules, $additional_rules );
        
        foreach( $rules as $field => $rule ){
            
            $type = $this->convertRuleTypeToSQLType( $rule );
            
            // Skip because the connection will be determined via secondary table
            if( $type === 'entity' || in_array( 'entity', $rule, true ) ){
                continue;
                
            // Skip because the connection will be determined via secondary table
            }elseif( $type === 'object' || in_array( 'object', $rule, true ) ){
                continue;
                
            // Self reference. Hierarchic construction. Adding service field 'parent'
            }elseif( $type === 'self' ){
                $columns[] = new Column( [
                    'field' => 'parent',
                    'type'  => $this->convertRuleTypeToSQLType( $this->entity_classname::$rules['id'] ),
                    'null'  => 'YES',
                ] );
                
            // Usual field. Direct representation into SQL-schema
            }else{
                $columns[] = new Column( [
                    'field' => $rule['field'] ?? $field,
                    'type'  => $type,
                    'null'  => in_array( 'required', $rule, true ) ? 'NO' : 'YES',
                    'default' => $rule['default'] ?? null,
                    'extra'   => $rule['extra']   ?? null,
                ] );
            }
        }
        
        return $columns;
    }
    
    /**
     * Convert rules to SQL-types and some additional special types for inner purposes
     *
     * @param array $rule
     *
     * @return string
     */
    private function convertRuleTypeToSQLType( array $rule ): string
    {
        $type = $rule['type'];
        
        // Object type
        if( class_exists( $type ) ){
            
            // Self
            if( $type === $this->entity_classname ){
                $type = 'self';
                
            // Other EntityObject
            }elseif( is_subclass_of($type, EntityObject::class ) ){
                $type = 'entity';
                
            // Single or multiple ValueObject
            }elseif( is_subclass_of($type, ValueObject::class ) ){
                if( $this->objects_as_json ){
                    $length = ( $rule['length'] ?? 1 ) * $this->default_object_length;
                    $type   = $length < 16383
                        ? 'VARCHAR(' . $length . ')'
                        : 'TEXT';
                }else{
                    $type = 'object';
                }
            }
            
        // Scalar type
        }elseif( is_scalar( $type ) ){
            
            $type = match( $type ){
                'string' => isset( $rule['content'] ) && is_array( $rule['content'])
                    ? 'ENUM(\'' . implode( "','", $rule['content'] ) . '\')'
                    : 'VARCHAR(' . ( $rule['length'] ?? $this->default_string_length ) . ')',
                'integer' => 'INT(' . ( $rule['length'] ?? $this->default_integer_length ) . ')',
            };
            
        }
        
        return $type;
    }
}