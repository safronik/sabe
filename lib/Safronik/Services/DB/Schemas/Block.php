<?php

namespace Safronik\Services\DB\Schemas;

final class Block extends SchemaAbstract
{
    protected static string $table = 'blocks';
    protected static array  $columns = [
        
        // Required
        [ 'field' => 'id',       'null' => 'no',  'type' => 'VARCHAR(64)', ],
        [ 'field' => 'name',     'null' => 'no',  'type' => 'VARCHAR(64)', ],
        [ 'field' => 'tag',      'null' => 'no',  'type' => "ENUM('div','header','footer','main','article','aside','section','nav')", ],
        
        // Optional
        [ 'field' => 'parent',   'null' => 'yes', 'type' => 'VARCHAR(64)',],
        [ 'field' => 'content',  'null' => 'yes', 'type' => 'VARCHAR(4096)',],
        [ 'field' => 'css',      'null' => 'yes', 'type' => 'TEXT',           'default' => 'width: 100px; height: 100px;'],
    ];
    protected static array  $indexes = [
        [ 'name' => 'PRIMARY', 'type' => 'KEY',          'body' => '(`id`)' ],
        [ 'name' => 'parent',  'type' => 'UNIQUE INDEX', 'body' => '(`parent`)' ],
    ];
}