<?php

namespace Safronik\Core\ErrorsProcessing;

use Safronik\Core\Config\Mode;
use Safronik\Core\Extensions\ModeExtension;
use Safronik\Core\Logger;

class ErrorHandler
{
    use ModeExtension;

    public function __construct( string $mode = null, array $customSettings = [] )
    {
        $this->mode            = $mode           ?? self::DEFAULT_MODE;
        $this->currentSettings = $customSettings ?: Mode::from( $this->mode )->getErrorSettings();

        $this->setupMode( $this->currentSettings );

        register_shutdown_function( [ $this, 'catchErrors' ] );
    }

    private function setupMode( array $settings ): void
    {
        error_reporting( $settings[ 'error_reporting'] );
        ini_set( 'display_errors', $settings[ 'display_errors'] );
        $settings[ 'catch_buffer'] && ob_start();
    }


    public function catchErrors(): void
    {
        $error = error_get_last();

        if( ! $this->isErrorToHandle( $error ) ){
            return;
        }

        $this->handleError( $error );
    }

    private function isErrorToHandle( ?array $error ): bool
    {
        return $error && in_array(
            $error['type'],
                $this->currentSettings[ 'error_types_to_handle'],
            true
        );
    }

    private function handleError( array $error ): void
    {
        // No output for users
        ob_end_clean();

        // Log error
        Logger::log(
            $error['message'],
            $this->currentSettings[ 'log_file'],
        );

        // Respond with placeholder screen
        http_response_code( 500 );

        // - вернуть после заголовка данные для пользователя
        echo $error['message'];
    }

    public function __destruct()
    {
        ob_get_contents() && ob_end_flush();
    }
}