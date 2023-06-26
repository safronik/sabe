<?php

namespace Safronik\Apps\REST;

class SQLScheme
{
    protected static $schema_prefix = 'cms_';
    protected static $schemas = [
        'routes' => [
            'columns' => [
                [ 'field' => 'route', 'type' => 'VARCHAR(255)', 'null' => 'no', ],
                [ 'field' => 'access', 'type' => 'VARCHAR(163)', 'null' => 'no', 'default' => 'guest' ],
                [ 'field' => 'app', 'type' => 'VARCHAR(1024)', 'null' => 'yes', 'default' => 'null', ],
            ],
            'indexes' => [
                [ 'type' => 'PRIMARY', 'name' => 'KEY', 'body' => '(`page`)' ],
            ],
        ],
    ];
}