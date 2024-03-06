<?php

$db_config = [
    'connection' => null,
    'driver'     => 'PDO', // pdo, mysqli, wordpress (case insensitive)
    'hostname'   => 'db', // Could be a container name
    'port'       => 3306,
    'charset'    => 'utf8',
    'database'   => 'sabe',
    'username'   => 'root',
    'password'   => 'root',
    'dsn'        => '', // Compiles automatically, but you can set it manually
    'options'    => [],
    'db_prefix'  => '', // Prefix to all the tables for this app. Could be useful if you run a few applications on the same database
];