<?php

namespace Safronik\Core\CodeTemplates\Interfaces;

interface Multitonable
{
    public static function getInstance( ...$params ): mixed;
    public static function isInitialized(): bool;
}