<?php

namespace Safronik\Core\CodeTemplates\Interfaces;

interface Serviceable
{
    public static function getAlias(): ?string;
    public static function getGatewayAlias(): ?string;
}