<?php

namespace Safronik\Models\Entities\SchemaProviders;

use Safronik\DBMigrator\Exceptions\DBMigratorException;
use Safronik\DBMigrator\Objects\Constraint;
use Safronik\DBMigrator\Objects\Index;
use Safronik\DBMigrator\Objects\Table;
use Safronik\Models\Entities\SchemaProviders\MetaObjects\MetaEntity;
use Safronik\Models\Entities\SchemaProviders\MetaObjects\MetaValue;

class ValueSchemaProvider extends BaseSchemaProvider
{
    protected MetaValue|MetaEntity $object;
    
    private string $id_column_name;
    private string $table;
    
    public function __construct( MetaValue $object )
    {
        parent::__construct( $object );

        $this->table = $this->object->table;
        $this->id_column_name = array_key_first($this->object->rules );
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
            $this->compileColumnsFromRules( $this->object->rules ),
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
                'key_name' => 'id',
                'columns'  => [ $this->id_column_name, ],
                // 'unique'   => true,
            ] ),
        ];
    }

    /**
     * @return Constraint[]
     * @throws DBMigratorException
     */
    private function compileConstraints(): array
    {
        return [
            new Constraint([
                'name'             => 'FK_' . $this->object->parent->table . '__' . $this->object->table,
                'column'           => $this->id_column_name,
                'reference_table'  => $this->object->parent->table,
                'reference_column' => 'id',
            ]),
        ];
    }
}