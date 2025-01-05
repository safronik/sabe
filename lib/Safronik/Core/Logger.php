<?php

namespace Safronik\Core;

class Logger
{
    private string $destination;

    public function __construct( string $destination )
    {
        $this->destination = Config::get('dirs.root') . DIRECTORY_SEPARATOR . $destination;
    }

    /**
     * Logs a message
     *
     * @param mixed $message
     * @return void
     */
    public function log( mixed $message ): void
    {
        is_file( $this->destination )
            && file_put_contents( $this->destination, $message . PHP_EOL, FILE_APPEND );
    }
}