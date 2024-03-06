<?php

namespace Safronik\Services\DB\Schemas;

abstract class SchemaAbstract{
    
    protected static string $table;
    protected static array $columns;
    protected static array $indexes;
    
    public static function getTable(): string
    {
        return static::$table;
    }
    
    public static function getColumns(): array
    {
        return static::$columns;
    }
    
    public static function getIndexes(): array
    {
        return static::$indexes;
    }
    
    public static function getSchema(): array
    {
        return [
            static::$table => [
                'columns' => static::$columns,
                'indexes' => static::$indexes,
            ],
        ];
    }
}