<?php

namespace Safronik\Services\DB\Gateways;

use Safronik\Services\Visitor\DBVisitorGatewayInterface;

final class DBGatewayVisitor extends AbstractDBGateway implements DBVisitorGatewayInterface
{
    public function registerVisitor( $id, $ip, $ip_decimal, $user_agent, $browser_signature ): void
	{
		$this->db
            ->insert(
			    'visitors',
                [
                    'visitor_id'        => [ $id, 'string' ],
                    'ip'                => [ $ip, 'string' ],
                    'ip_decimal'        => [ $ip_decimal, 'int' ],
                    'user_agent'        => [ $user_agent, 'string' ],
                    'browser_signature' => [ $browser_signature, 'string' ],
                ],
                ['on_duplicate_key' => [ 'increment' => [ 'hits' ] ] ]
		);
	}
    
    public function getVisits( string $id ): array
    {
        return $this->db
            ->setResponseMode( 'array' )
            ->select(
                'visitors',
                [ 'hits' ],
                [
                    'id' => [ $id, 'string']
                ]
            );
    }
}