<?php

namespace Safronik\Core;

use Safronik\Core\Exceptions\ConfigException;

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

    protected static ?string $root = null;

    /**
     * Log a message
     *
     *  Usage example:
     *  - Logger::log('Hello world');           // Logs 'Hello world' to ROOT/logs/default.log
     *  - Logger::log('Hello world', 'my_log'); // Logs 'Hello world' to ROOT/logs/my_log.log
     *
     * @param mixed       $message
     * @param string|null $to
     *
     * @throws ConfigException
     */
    public static function log( mixed $message, ?string $to = null ): void
    {
        $to = $to
            ? static::getRoot() . DIRECTORY_SEPARATOR . static::DEFAULT_FOLDER . $to .                  static::EXTENSION
            : static::getRoot() . DIRECTORY_SEPARATOR . static::DEFAULT_FOLDER . static::DEFAULT_FILE . static::EXTENSION;

        file_put_contents(
            $to,
            $message . self::END_OF_LOG_MESSAGE,
            FILE_APPEND
        );
    }

    /**
     * Caches the root directory
     *
     * @throws ConfigException
     */
    private static function getRoot()
    {
        return static::$root ?? Config::get('dirs.root');
    }
}