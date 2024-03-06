<?php

namespace Safronik\Services\DB\Schemas;

final class Visitor extends SchemaAbstract
{
    protected static string $table = 'visitors';
    protected static array  $columns = [
        [ 'field' => 'id',                'type' => 'VARCHAR(64)',      'null' => 'no', ],
        [ 'field' => 'ip',                'type' => 'VARCHAR(15)',      'null' => 'no', ],
        [ 'field' => 'ip_decimal',        'type' => 'INT(10) UNSIGNED', 'null' => 'no', ],
        [ 'field' => 'browser_signature', 'type' => 'VARCHAR(255)',     'null' => 'no', ],
        [ 'field' => 'user_agent',        'type' => 'VARCHAR(1024)',    'null' => 'no', ],
        [ 'field' => 'hits',              'type' => 'INT(10) UNSIGNED', 'null' => 'no', 'default' => '1' ],
    ];
    protected static array  $indexes = [
        [ 'type' => 'PRIMARY', 'name' => 'KEY', 'body' => '(`id`)' ],
    ];
}