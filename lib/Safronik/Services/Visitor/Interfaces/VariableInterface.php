<?php

namespace Safronik\Services\Visitor\Interfaces;

interface VariableInterface
{
    public function registerVisitor( $id, $ip, $ip_decimal, $user_agent, $browser_signature ): void;
    public function getVisits( string $id): array;
}