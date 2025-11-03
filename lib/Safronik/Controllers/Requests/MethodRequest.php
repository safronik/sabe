<?php

namespace Safronik\Controllers\Requests;

use Safronik\Core\Helpers\ValidationHelper;
use Safronik\Router\Request;

abstract class MethodRequest extends Request
{
    protected array $rules = [];

    public static function getInstance( ...$params ): mixed
    {
        self::$instance = (new static( parent::getInstance( ...$params ) ) );
        self::$instance->validate();

        return self::$instance;
    }

    protected function validate(): void
    {
        ValidationHelper::validate(
            $this->parameters,
            $this->rules,
        );
    }
}