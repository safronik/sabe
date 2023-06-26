<?php

namespace Safronik\Blocks;

final class SQLScheme extends \Safronik\Services\DBStructureHandler\SQLScheme
{
    protected static $schema_prefix = 'sabe_';
    protected static $schemas = [
        'blocks' => [
            'columns' => [
                [ 'field' => 'block_id',   'type' => 'INT',                   'null' => 'no', 'extra' => 'AUTO_INCREMENT'],
                [ 'field' => 'block_name', 'type' => 'VARCHAR(64)',           'null' => 'no', ],
                [ 'field' => 'tag',        'type' => "ENUM('div','header','footer','main','article','aside','section','nav')", 'null' => 'no', 'default' => '"div"' ],
                [ 'field' => 'blocks',     'type' => 'VARCHAR(4096)',         'null' => 'yes', ],
                [ 'field' => 'elements',   'type' => 'VARCHAR(4096)',         'null' => 'yes', ],
                [ 'field' => 'css',        'type' => 'TEXT',                  'null' => 'yes', ],
            ],
            'indexes' => [
                [ 'name' => 'PRIMARY',    'type' => 'KEY',    'body' => '(`block_id`)' ],
                [ 'name' => 'block_name', 'type' => 'UNIQUE INDEX', 'body' => '(`block_name`)' ],
            ],
        ],
    ];
}