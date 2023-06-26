<?php

namespace Safronik\Services\DB\Gateways;

class DBGatewayElement extends AbstractDBGateway implements \Safronik\Elements\DBGatewayElementInterface
{
    public function getElementData( $element_id ): array
    {
        return $this->db
            ->select(
                'elements',
                [],
                [ 'element_id' => [ $element_id, 'string' ] ]
            );
    }
}