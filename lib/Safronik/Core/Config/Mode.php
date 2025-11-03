<?php

namespace Safronik\Core\Config;

enum Mode: string
{
    case Developing = 'developing';
    case Production = 'production';
    case Stage      = 'stage';

    private const PRODUCTION_ERROR_SETTINGS = [
        'error_reporting'       => 0,
        'display_errors'        => 'Off',
        'catch_buffer'          => true,
        'log_file'              => 'error.critical',
        'ERROR_TYPES_TO_HANDLE' => [],
    ];

    private const DEV_ERROR_SETTINGS = [
        'error_reporting'       => E_ALL ^ E_DEPRECATED,
        'display_errors'        => true,
        'catch_buffer'          => true,
        'log_file'              => 'error.critical',
        'error_types_to_handle' => [
            E_ERROR,
            E_PARSE,
            E_COMPILE_ERROR,
            E_CORE_ERROR,
        ],
    ];

    // @todo
    private const STAGE_ERROR_SETTINGS = [
        'log_file' => 'error.critical'
    ];

    // @todo
    private const PRODUCTION_EXCEPTION_SETTINGS = [
        'log_file' => 'error.critical'
    ];

    // @todo
    private const DEV_EXCEPTION_SETTINGS = [
        'log_file' => 'error.critical'
    ];

    // @todo
    private const STAGE_EXCEPTION_SETTINGS = [
        'log_file' => 'error.critical'
    ];

    public function getErrorSettings(): array
    {
        return match( $this )
        {
            self::Production  => self::PRODUCTION_ERROR_SETTINGS,
            self::Stage       => self::STAGE_ERROR_SETTINGS,
            self::Developing => self::DEV_ERROR_SETTINGS,
        };
    }

    public function getExceptionSettings(): array
    {
        return match( $this )
        {
            self::Production  => self::PRODUCTION_EXCEPTION_SETTINGS,
            self::Stage       => self::STAGE_EXCEPTION_SETTINGS,
            self::Developing => self::DEV_EXCEPTION_SETTINGS,
        };
    }
}
