<?php

namespace Safronik\Core\ErrorsProcessing;

use Safronik\Core\Config\Mode;
use Safronik\Core\Extensions\ModeExtension;

class ExceptionHandler
{
    use ModeExtension;

    public function __construct( string $mode = null, array $customSettings = [] )
    {
        $this->mode            = $mode           ?? self::DEFAULT_MODE;
        $this->currentSettings = $customSettings ?: Mode::from( $this->mode )->getExceptionSettings();

        $this->setupMode( $this->currentSettings );
    }

    private function setupMode( array $settings ): void
    {

    }

    public function __destruct()
    {

    }
}