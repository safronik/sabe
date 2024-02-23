<?php

namespace Safronik\Services\Options;

class SQLScheme
{
    public static array $scheme = [
        'options' => [
            'columns' => [
                [ 'field' => 'id',           'type' => 'int',                               'null' => 'no', 'extra' => 'AUTO_INCREMENT'],
                [ 'field' => 'affiliation',  'type' => 'varchar(50)',  'default' => 'NULL', ],
                [ 'field' => 'option_name',  'type' => 'varchar(50)',  'default' => 'NULL', ],
                [ 'field' => 'option_value', 'type' => 'mediumtext',   'default' => 'NULL', ],
            ],
            'indexes' => [
                [ 'type' => 'PRIMARY', 'name' => 'KEY',         'body' => '(`id`)' ],
                [ 'type' => 'KEY',     'name' => 'option_name', 'body' => '(`option_name`)' ],
            ],
        ],
    ];
}