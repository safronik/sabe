<?php

namespace Safronik\Apps\Orange;

final class SQLScheme extends \Safronik\Services\DBStructureHandler\SQLScheme
{
    public static $schema_prefix = '';
    public static $schemas = [
        'companies' => [
            'columns' => [
                [ 'field' => 'id',      'type' => 'INT(11)',       'null' => 'no', ],
                [ 'field' => 'ticker', 'type' => 'VARCHAR(255)',   'null' => 'no', ],
                [ 'field' => 'added',  'type' => 'DATETIME',       'null' => 'no',  'default' => 'CURRENT_TIMESTAMP', ],
            ],
            // 'indexes' => [
            //     [ 'type' => 'PRIMARY', 'name' => 'KEY', 'body' => '(`id`)' ],
            // ],
        ],
        'news' => [
            'columns' => [
                [ 'field' => 'id',                'type' => 'INT(11)', 'null' => 'no', 'extra' => 'AUTO_INCREMENT', ],
                [ 'field' => 'companiesAffected', 'type' => 'VARCHAR(15000)', 'null' => 'no', ],
                [ 'field' => 'date',              'type' => 'DATETIME', 'null' => 'no', 'default' => 'CURRENT_TIMESTAMP', ],
                [ 'field' => 'rate',              'type' => 'INT(11)', 'null' => 'no', ],
                [ 'field' => 'text',              'type' => 'VARCHAR(1024)', 'null' => 'no', ],
            ],
            // 'indexes' => [
            //     [ 'type' => 'PRIMARY', 'name' => 'KEY', 'body' => '(`id`)' ],
            // ],
        ],
        'short_strategies' => [
            'columns' => [
                [ 'field' => 'id',           'type' => 'INT(11)', 'null' => 'no', ],
                [ 'field' => 'ticker',       'type' => 'VARCHAR(1024)', 'null' => 'no', ],
                [ 'field' => 'created',      'type' => 'DATETIME', 'null' => 'no', 'default' => 'CURRENT_TIMESTAMP', ],
                [ 'field' => 'median_price', 'type' => 'INT(11)', 'null' => 'no', ],
                [ 'field' => 'current_price', 'type' => 'INT(11)', 'null' => 'no', ],
                [ 'field' => 'trend',        'type' => 'INT(11)', 'null' => 'no', ],
                [ 'field' => 'goal',         'type' => 'INT(11)', 'null' => 'no', ],
                [ 'field' => 'comment',         'type' => 'VARCHAR(1024)', 'null' => 'no', ],
            ],
        ],
        // 'medium_strategies' => [
        //     'columns' => [
        //         [ 'field' => 'ticker_id',    'type' => 'INT(11)', 'null' => 'no', ],
        //         [ 'field' => 'ticker',    'type' => 'INT(11)', 'null' => 'no', ],
        //         [ 'field' => 'created',      'type' => 'DATETIME', 'null' => 'no', 'default' => 'CURRENT_TIMESTAMP', ],
        //         [ 'field' => 'median_price', 'type' => 'INT(11)', 'null' => 'no', ],
        //         [ 'field' => 'trend',        'type' => 'INT(11)', 'null' => 'no', ],
        //         [ 'field' => 'text',         'type' => 'VARCHAR(1024)', 'null' => 'no', ],
        //     ],
        // ],
        // 'long_strategies' => [
        //     'columns' => [
        //         [ 'field' => 'ticker_id',         'type' => 'INT(11)', 'null' => 'no', ],
        //         [ 'field' => 'created',           'type' => 'DATETIME', 'null' => 'no', 'default' => 'CURRENT_TIMESTAMP', ],
        //         [ 'field' => 'updated',           'type' => 'DATETIME', 'null' => 'no', 'default' => 'CURRENT_TIMESTAMP', ],
        //         [ 'field' => 'rate',              'type' => 'INT(11)', 'null' => 'no', ],
        //         [ 'field' => 'text',              'type' => 'VARCHAR(1024)', 'null' => 'no', ],
        //     ],
        // ],
    ];
}