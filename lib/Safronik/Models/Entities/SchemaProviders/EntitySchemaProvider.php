<?php

namespace Safronik\Models\Entities\SchemaProviders;

use Safronik\DBMigrator\Objects\Index;
use Safronik\DBMigrator\Objects\Table;
use Safronik\Models\Entities\SchemaProviders\MetaObjects\MetaEntity;
use Safronik\Models\Entities\SchemaProviders\MetaObjects\MetaValue;

class EntitySchemaProvider extends BaseSchemaProvider
{
    /**
     * Returns entity table schema without secondary tables
     *
     * @return Table
     * @throws \Exception
     */
    public function getSchema(): Table
    {
        return new Table(
            $this->object->table,
            $this->compileColumnsFromRules( $this->object->rules ),
            $this->compileIndexes()
        );
    }

    /**
     * Returns schema for value objects of the entity
     *
     * @return Table[]
     * @throws \Exception
     */
    public function getEntitiesSchema(): array
    {
        $schemas = [];
        foreach( $this->object->rules as $rule ){
            if( in_array($this->getRelationType( $rule ), [ self::RELATION_ENTITY_TO_MANY, self::RELATION_ENTITY_TO_ONE ] ) ){
                $entity          = new MetaEntity( $rule->type, $this->object->root_namespace, $this->object->class );
                $schema_provider = new EntitySchemaProvider( $entity );
                $schemas[]       = $schema_provider->getSchema();
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
    public function getValuesSchema(): array
    {
        $schemas = [];
        foreach( $this->object->rules as $rule ){
            if( in_array($this->getRelationType( $rule ), [ self::RELATION_OBJECT_TO_MANY, self::RELATION_OBJECT_TO_ONE ] ) ){
                $value           = new MetaValue( $rule->type, $this->object->root_namespace, $this->object->class );
                $schema_provider = new ValueSchemaProvider( $value );
                $schemas[]       = $schema_provider->getSchema();
            }
        }

        return $schemas;
    }

    /**
     * Creates secondary tables to support ORM
     *
     * @return Table[]
     * @throws \Exception
     */
    public function getRelationsSchema(): array
    {
        $schemas = [];
        foreach( $this->object->rules as $rule ){
            if( $this->getRelationType( $rule ) === self::RELATION_ENTITY_TO_MANY ){
                $secondary_object = new MetaEntity( $rule->type, $this->object->root_namespace );
                $schema_provider  = new RelationSchemaProvider( $this->object, $secondary_object );
                $schemas[]        = $schema_provider->getSchema();
            }
        }

        return $schemas;
    }

    /**
     * Get indexes for the entity
     *
     * @return Index[]
     * @throws \Exception
     */
    private function compileIndexes(): array
    {
        return [
            new Index( [ 'key_name' => 'PRIMARY', 'columns' => [ 'id' ], 'comment' => 'Primary ID' ] )
        ];
    }
}