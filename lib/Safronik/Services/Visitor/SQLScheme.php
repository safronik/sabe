<?php

namespace Safronik\Services\Visitor;

class SQLScheme
{
    public static $scheme = [
        'visitors' => [
            'columns' => [
                [ 'field' => 'visitor_id',        'type' => 'VARCHAR(64)',   'null' => 'no', ],
                [ 'field' => 'ip',                'type' => 'VARCHAR(15)',   'null' => 'no', ],
                [ 'field' => 'ip_decimal',        'type' => 'VARCHAR(15)',   'null' => 'no', ],
                [ 'field' => 'browser_signature', 'type' => 'VARCHAR(255)',  'null' => 'no', ],
                [ 'field' => 'user_agent',        'type' => 'VARCHAR(1024)', 'null' => 'no', ],
                [ 'field' => 'hits',              'type' => 'INT UNSIGNED',  'null' => 'no', 'default' => '1' ],
            ],
            'indexes' => [
                [ 'type' => 'PRIMARY', 'name' => 'KEY', 'body' => '(`visitor_id`)' ],
            ],
        ],
    ];
}