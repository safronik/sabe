<?php

namespace Safronik\Core\CodeTemplates\Interfaces;

interface Installable
{
    public static function getScheme(): ?array;
    public static function getSlug(): ?string;
    public static function getOptions(): ?array;
}