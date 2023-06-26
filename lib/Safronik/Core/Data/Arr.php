<?php

namespace Safronik\Core\Data;

class Arr
{
    /**
     * Modifies the array $array. Paste $insert on $position
     *
     * @param array $array
     * @param int|string $position
     * @param mixed $insert
     */
    public static function insert(&$array, $position, $insert)
    {
        if (is_int($position)) {
            array_splice($array, $position, 0, $insert);
        } else {
            $pos   = array_search($position, array_keys($array));
            $array = array_merge(
                array_slice($array, 0, $pos),
                $insert,
                array_slice($array, $pos)
            );
        }
    }

}