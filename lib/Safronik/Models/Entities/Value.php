<?php

namespace Safronik\Models\Entities;

abstract class Value extends Obj{
    
    /**
     * @param string $json_string
     * @param bool   $as_object
     *
     * @return array|Obj
     * @throws \JsonException
     */
    protected function decodeJSON( string $json_string, bool $as_object = true ): array|Obj
    {
        return json_decode( $json_string, $as_object, 512, JSON_THROW_ON_ERROR );
    }
}