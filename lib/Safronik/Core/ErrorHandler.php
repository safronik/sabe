<?php

namespace Safronik\Core;

class ErrorHandler
{
    private const MODE_PROD  = 'production';
    private const MODE_STAGE = 'stage';
    private const MODE_DEV   = 'dev';

    private const DEFAULT_MODE = self::MODE_DEV;

    private const MODE_PROD_OPTIONS  = [
        'error_reporting'       => 0,
        'display_errors'        => 'Off',
        'catch_buffer'          => true,
        'log_file'              => 'error.critical',
        'ERROR_TYPES_TO_HANDLE' => [],
    ];

    private const MODE_STAGE_OPTIONS = [
        // @todo
        'log_file'              => self::MODE_STAGE. '.error.critical'
    ];

    private const MODE_DEV_OPTIONS   = [
        'error_reporting'       => E_ALL ^ E_DEPRECATED,
        'display_errors'        => true,
        'catch_buffer'          => true,
        'log_file'              => self::MODE_DEV. '.error.critical',
        'error_types_to_handle' => [
            E_ERROR,
            E_PARSE,
            E_COMPILE_ERROR,
            E_CORE_ERROR,
        ],
    ];

    private const MODES = [
        self::MODE_PROD  => self::MODE_PROD_OPTIONS,
        self::MODE_STAGE => self::MODE_STAGE_OPTIONS,
        self::MODE_DEV   => self::MODE_DEV_OPTIONS,
    ];

    private string $mode;
    private array  $customOptions;
    private array  $currentOptions;

    public function __construct( string $mode = null, array $customOptions = [] )
    {
        $this->mode           = $mode ?? self::DEFAULT_MODE;
        $this->customOptions  = $customOptions;
        $this->currentOptions = $this->customOptions ?: self::MODES[ $this->mode ];

        $this->setupMode( $this->currentOptions );

        register_shutdown_function( [ $this, 'catchErrors' ] );
    }

    private function setupMode( array $mode ): void
    {
        error_reporting( $mode['error_reporting'] );
        ini_set( 'display_errors', $mode['display_errors'] );
        $mode['catch_buffer'] && ob_start();
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
            $this->currentOptions['error_types_to_handle'],
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
            $this->currentOptions['log_file'],
        );

        // Respond with placeholder screen
        http_response_code( 500 );

        // - вернуть после заголовка данные для пользователя

    }

    public function __destruct()
    {
        ob_end_flush();
    }
}