<?php

namespace Safronik\Services\DB\Gateways;

use Safronik\Services\User\DBUserGatewayInterface;

final class DBGatewayUser extends AbstractDBGateway implements DBUserGatewayInterface
{
    public function getUserBy( $type, $needle ): array
    {
        $type = $type === 'id' ? 'user_id' : $type;
        
        return $this->db
            ->setResponseMode( 'array' )
            ->select(
                'users',
                [],
                [
                    $type =>   [ $needle,],
                ]
            )[0] ?? [];
    }
}