<?php

namespace Safronik\Models\Entities\SchemaProviders;

use Safronik\DBMigrator\Exceptions\DBMigratorException;
use Safronik\DBMigrator\Objects\Column;
use Safronik\DBMigrator\Objects\Table;
use Safronik\Models\Entities\Entity;
use Safronik\Models\Entities\Rule;
use Safronik\Models\Entities\Obj;
use Safronik\Models\Entities\SchemaProviders\MetaObjects\MetaEntity;
use Safronik\Models\Entities\Value;

abstract class BaseSchemaProvider
{
    protected const RELATION_SCALAR =           'scalar';

    protected const RELATION_ENTITY_RECURSION   = 'entity_recursion';
    protected const RELATION_ENTITY_TO_MANY =   'entity_relation';
    protected const RELATION_ENTITY_TO_ONE  =   'entity';

    protected const RELATION_OBJECT_TO_MANY =   'object_relation';
    protected const RELATION_OBJECT_TO_ONE  =    'object';

    protected MetaEntity $object;
    
    private bool $objects_as_json = false;
    private int  $default_string_length = 64;
    private int  $default_integer_length = 11;
    
    public function __construct( MetaEntity $object )
    {
        $this->object = $object;
    }
    
    /**
     * Returns entity table schema without secondary tables
     *
     * @return Table
     * @throws \Exception
     */
    abstract public function getSchema(): Table;


//    abstract public function getSchemas(): array;

    /**
     * Returns entity columns SQL-scheme
     *
     * @param Rule[] $rules
     *
     * @return Column[]
     * @throws DBMigratorException
     */
    protected function compileColumnsFromRules( array $rules ): array
    {
        $columns = [];
        
        foreach( $rules as $field => $rule ){
            
            $relation_type = $this->getRelationType( $rule );
            $type          = $this->convertRuleTypeToSQLType( $rule, $relation_type );
            
            switch( $relation_type ){
                
                // Skip because the connection will be determined via secondary table
                case self::RELATION_ENTITY_TO_MANY: continue 2;
                case self::RELATION_OBJECT_TO_MANY: continue 2;
                case self::RELATION_OBJECT_TO_ONE:  continue 2;

                // Self reference. Hierarchic construction. Adding service field 'parent'
                case self::RELATION_ENTITY_RECURSION:
                    $columns[] = new Column( [
                        'field' => 'parent',
                        'type'  => $this->convertRuleTypeToSQLType( $this->object->rules['id'], 'scalar' ),
                        'null'  => 'YES',
                    ] );
                    break;
                    
                default:
                    $columns[] = new Column( [
                        'field' => $field,
                        'type'  => $type,
                        'null'  => $rule->required ? 'NO' : 'YES',
                        'default' => $rule->default ?? null,
                        'extra'   => $rule->extra   ?? null,
                    ] );
                    break;
            }
        }
        
        return $columns;
    }
    
    /**
     * Convert rules to SQL-types and some additional special types for inner purposes
     *
     * @param Rule $rule
     * @param            $relation_type
     *
     * @return string|null
     */
    protected function convertRuleTypeToSQLType( Rule $rule, $relation_type ): ?string
    {
        $type = $rule->type;
        
        return match ( $relation_type ) {
            'scalar' => match ( $type ) {
                'integer' => 'INT(' . ( $rule->length ?? $this->default_integer_length ) . ')',
                'string'  => isset( $rule->content ) && is_array( $rule->content )
                    ? 'ENUM(\'' . implode( "','", $rule->content ) . '\')'
                    : 'VARCHAR(' . ( $rule->length ?? $this->default_string_length ) . ')',
            },
            'entity' => $this->convertRuleTypeToSQLType( $rule->type::rule( 'id' ), 'scalar' ),
            'object' => 'TEXT',
            default  => null,
        };
    }
    
    public function getRelationType( Rule $rule ): string
    {
        // Do not change the order of cases
        return match ( true ) {

            // Scalar
            $rule->isScalar()                          => self::RELATION_SCALAR,

            // Entities
            $rule->type === $this->object->class       => self::RELATION_ENTITY_RECURSION,
            $rule->isEntity() &&   $rule->isMultiple() => self::RELATION_ENTITY_TO_MANY,
            $rule->isEntity() && ! $rule->isMultiple() => self::RELATION_ENTITY_TO_ONE,

            // Objects
            $rule->isObject() &&   $rule->isMultiple() => self::RELATION_OBJECT_TO_MANY,
            $rule->isObject() && ! $rule->isMultiple() => self::RELATION_OBJECT_TO_ONE,
        };
    }
    
    public function buildTree()
    {
    
    }
}