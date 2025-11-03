<?php

namespace Safronik\Core\Config;

enum Defaults
{
    case app;

    private const APP_VALUE = [
        'name' => 'Unknown',
        'version' => '1.0.0',
        'mode' => 'developing',
    ];

    case service_words;

    private const SERVICE_WORDS_VALUE = [
        'controller',
        'service',
        'gateway',
        'repository',
        'view',
    ];

    public function value(): mixed
    {
        return match( $this )
        {
            self::service_words => self::SERVICE_WORDS_VALUE,
            self::app           => self::APP_VALUE,
        };
    }

    public static function get(): array
    {
        $names = array_map(
            static fn( $value ) => $value->name,
            self::cases()
        );

        $values = array_map(
            static fn( $value ) => $value->value(),
            self::cases()
        );

        return array_combine( $names, $values );
    }
}
