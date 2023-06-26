<?php

namespace Safronik\Services\DB\Gateways;

use Safronik\Services\Options\DBOptionGatewayInterface;

final class DBGatewayOptions extends AbstractDBGateway implements DBOptionGatewayInterface
{
    public function getOptionsByGroup( $group ): array
    {
        return $this->db
            ->select(
                'options',
                ['option_name'],
                [
                    'affiliation' => [$group],
                ]
            );
    }
    
    public function loadOption( $option, $group ): mixed
    {
        return $this->db
            ->select(
                'options',
                ['option_value'],
                [
                    'option_name' => [$option],
                    'affiliation' => [$group],
                ]
            )['option_value'] ?? null;
    }
    
    public function saveOption( $option, $group, $value ): int
    {
        return $this->db->update(
            'options',
            [
                'option_value' => [ $value ]
            ],
            [
                'option_name' => [$option],
                'affiliation' => [$group],
            ]
        );
    }
    
    public function removeOption( $option, $group ): int
    {
        return $this->db->delete(
            'options',
            [
                'option_name' => [$option],
                'affiliation' => [$group],
            ]
        );
    }
}