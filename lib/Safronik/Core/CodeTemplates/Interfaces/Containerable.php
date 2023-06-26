<?php

namespace Safronik\Core\CodeTemplates\Interfaces;

interface Containerable
{
    public static function get( string $alias, mixed $params = [] ): mixed;
    public static function has( string $service ): bool;
}