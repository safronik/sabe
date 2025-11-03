<?php

namespace Safronik\Core;

use Exception;

/**
 * Class Logger
 *
 * @package Safronik\Core
 * @version 1.1
 *
 * Static class for logging messages
 *
 * Usage example:
 * - Logger::log('Hello world');           // Logs 'Hello world' to default.log
 * - Logger::log('Hello world', 'my_log'); // Logs 'Hello world' to my_log.log
 */
class Logger
{
    protected const DEFAULT_FOLDER     = 'logs' . DIRECTORY_SEPARATOR;
    protected const DEFAULT_FILE       = self::DEFAULT_FOLDER . 'default';
    protected const EXTENSION          = '.log';
    protected const END_OF_LOG_MESSAGE = "\n\n";

    /**
     * Logs a message
     *
     *  Usage example:
     *  - Logger::log('Hello world');           // Logs 'Hello world' to ROOT/logs/default.log
     *  - Logger::log('Hello world', 'my_log'); // Logs 'Hello world' to ROOT/logs/my_log.log
     *
     * @param mixed $message
     * @param string|null $to
     * @param string|null $app
     *
     * @throws Exception
     */
    public static function log( mixed $message, ?string $to = null, ?string $app = null ): void
    {
        $root = $app
            ? Apps::get( $app )->config->get('dirs.root')
            : ROOT_DIR . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . 'base';

        file_put_contents(
            $root . DIRECTORY_SEPARATOR . static::DEFAULT_FOLDER . ( $to ?? static::DEFAULT_FILE ). static::EXTENSION,
            $message . static::END_OF_LOG_MESSAGE,
            FILE_APPEND
        );
    }
}