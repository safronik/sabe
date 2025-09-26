<?php

namespace Safronik\Middleware;

interface MiddlewareInterface
{
    public function execute( array $parameters = [] ): void;
}