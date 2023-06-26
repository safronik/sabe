<?php

namespace Safronik\Services\Visitor;

interface DBVisitorGatewayInterface
{
    public function registerVisitor( $id, $ip, $ip_decimal, $user_agent, $browser_signature ): void;
    public function getVisits( string $id): array;
}