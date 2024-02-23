<?php

namespace Safronik\Elements;

final class SQLScheme extends \Safronik\Services\DBStructureHandler\SQLScheme
{
    protected static $schema_prefix = 'sabe_';
    protected static $schemas = [
        'elements' => [
            'columns' => [
                [ 'field' => 'element_id',   'type' => 'INT',            'null' => 'no', 'extra' => 'AUTO_INCREMENT'],
                [ 'field' => 'element_name', 'type' => 'VARCHAR(64)',    'null' => 'no', ],
                [ 'field' => 'type',         'type' => 'VARCHAR(64)',    'null' => 'no', ],
                [ 'field' => 'elements',     'type' => 'VARCHAR(4096)',  'null' => 'yes', ],
                [ 'field' => 'css',          'type' => 'TEXT',           'null' => 'yes', ],
                [ 'field' => 'content',      'type' => 'TEXT',           'null' => 'yes', ],
            ],
            'indexes' => [
                [ 'name' => 'PRIMARY',    'type' => 'KEY',          'body' => '(`element_id`)' ],
                [ 'name' => 'block_name', 'type' => 'UNIQUE INDEX', 'body' => '(`element_name`)' ],
            ],
        ],
    ];
}