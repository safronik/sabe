<?php

namespace Safronik\Services;

interface Serviceable
{
    public static function getAlias(): ?string;
    public static function getGatewayAlias(): ?string;
}