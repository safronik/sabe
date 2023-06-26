<?php

namespace Safronik\Core\Exceptions;

class Uncaught extends Exception
{
    public static function handler()
    {
        return null;
    }
}