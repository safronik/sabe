<?php

namespace Safronik\Services\User;

class SQLScheme
{
    public static array $scheme = [
        'users' => [
            'columns' => [
                [ 'field' => 'user_id',    'type' => 'int',         'null' => 'no', 'extra' => 'AUTO_INCREMENT', ],
                [ 'field' => 'login',      'type' => 'varchar(20)', 'null' => 'no', ],
                [ 'field' => 'email',      'type' => 'VARCHAR(50)', 'null' => 'no', ],
                [ 'field' => 'pass',       'type' => 'varchar(64)', 'null' => 'no', ],
                [ 'field' => 'user_group', 'type' => 'varchar(20)', 'null' => 'no', ],
                [ 'field' => 'allow',      'type' => 'TINYINT',     'null' => 'no', ],
                [ 'field' => 'ssid',       'type' => 'varchar(64)', 'null' => 'no', ],
                [ 'field' => 'visitor_id', 'type' => 'varchar(64)', 'null' => 'no', ],
            ],
            'indexes' => [
                [ 'type' => 'PRIMARY', 'name' => 'KEY',   'body' => '(`user_id`)' ],
                [ 'type' => 'KEY',     'name' => 'login', 'body' => '(`login`)' ],
                [ 'type' => 'KEY',     'name' => 'email', 'body' => '(`email`)' ],
            ],
        ],
    ];
}