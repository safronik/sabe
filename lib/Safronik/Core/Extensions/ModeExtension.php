<?php

namespace Safronik\Core\Extensions;

use Safronik\Core\Config\Mode;

trait ModeExtension
{
    private const DEFAULT_MODE = Mode::Developing->value;

    private string $mode;
    private array  $currentSettings;
}