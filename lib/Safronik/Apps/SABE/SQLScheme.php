<?php

namespace Safronik\Apps\CMS;

final class SQLScheme extends \Safronik\Services\DBStructureHandler\SQLScheme
{
    protected static $schema_prefix = 'cms_';
    protected static $schemas = [
        'pages' => [
            'columns' => [
                [ 'field' => 'page',   'type' => 'VARCHAR(255)',  'null' => 'no', ],
                [ 'field' => 'access', 'type' => 'VARCHAR(163)',  'null' => 'no', 'default' => 'guest' ],
                [ 'field' => 'title',  'type' => 'VARCHAR(255)',  'null' => 'yes', 'default' => 'null', ],
                [ 'field' => 'meta',   'type' => 'VARCHAR(1024)', 'null' => 'yes', 'default' => 'null', ],
                [ 'field' => 'blocks', 'type' => 'VARCHAR(511)',  'null' => 'yes', 'default' => 'null', ],
            ],
            'indexes' => [
                [ 'type' => 'PRIMARY', 'name' => 'KEY', 'body' => '(`page`)' ],
            ],
        ],
    ];
}