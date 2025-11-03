<?php

namespace Safronik\Models\Entities\SchemaProviders;

use Safronik\DBMigrator\Objects\Column;
use Safronik\DBMigrator\Objects\Constraint;
use Safronik\DBMigrator\Objects\Index;
use Safronik\DBMigrator\Objects\Table;
use Safronik\Models\Entities\Rule;
use Safronik\Models\Entities\SchemaProviders\MetaObjects\MetaEntity;
use Safronik\Models\Entities\SchemaProviders\MetaObjects\MetaValue;

class RelationSchemaProvider extends BaseSchemaProvider{

    public const RELATION_MARK = '__to__';

    private MetaEntity $secondary;
    
    private array  $relation_rules;
    private string $primary_column;
    private string $secondary_column;
    private string $table;
    
    public function __construct( MetaEntity $primary, MetaEntity $secondary )
    {
        parent::__construct( $primary );
        
        $this->secondary        = $secondary;
        $this->table            = $this->object->table . self::RELATION_MARK . $this->secondary->table;
        $this->primary_column   = $this->object->table . '_id';
        $this->secondary_column = $this->secondary->table . '_id';
        
        $this->relation_rules = [
            $this->primary_column => new Rule(
                [ 'type' => $this->object->rules[ 'id' ]->type, 'length' => $this->object->rules[ 'id' ]->length, 'null' => 'NO' ],
                $this->primary_column
            ),
            $this->secondary_column => new Rule(
                [ 'type' => $this->secondary->rules[ 'id' ]->type, 'length' => $this->secondary->rules[ 'id' ]->length, 'null' => 'NO' ],
                $this->secondary_column
            ),
        ];
    }
    
    /**
     * Returns entity table schema without secondary tables
     *
     * @return Table
     * @throws \Exception
     */
    public function getSchema(): Table
    {
        return new Table(
            $this->table,
            $this->compileColumnsFromRules( $this->relation_rules ),
            $this->compileIndexes(),
            $this->compileConstraints()
        );
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
            new Index( [
                'key_name' => $this->primary_column,
                'columns'  => [ $this->primary_column, $this->secondary_column, ],
                'unique'   => true,
            ] ),
        ];
    }
    
    private function compileConstraints(): array
    {
        return [
            new Constraint( [
                'name'             => 'FK_' . $this->object->table . '_' . $this->secondary->table,
                'column'           => $this->primary_column,
                'reference_table'  => $this->object->table,
                'reference_column' => 'id',
            ] ),
            new Constraint( [
                'name'             => 'FK_' . $this->secondary->table . '_' . $this->object->table,
                'column'           => $this->secondary_column,
                'reference_table'  => $this->secondary->table,
                'reference_column' => 'id',
            ] ),
        ];
    }
    
}