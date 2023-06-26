<?php

namespace Safronik\Services\DB\Gateways;

class DBGatewayBlock extends AbstractDBGateway implements \Safronik\Blocks\DBGatewayBlockInterface
{
    
    public function getBlockData( $block_name = null, $block_id = null ): array
    {
        $where = [];
        if( $block_name ){
            $where['block_name'] = [ $block_name, 'string' ];
        }

        if( $block_id ){
            $where['block_id'] = [ $block_id, 'string' ];
        }
        
        if( ! $where ){
            throw new \Exception('No ID or NAME passed');
        }
        
        return $this->db
                ->setResponseMode( 'array' )
                ->select(
                    'blocks',
                    [],
                    [ 'block_name' => [ $block_name, 'string' ] ]
                );
    }
    
    public function getBlockDataByName( $block_name ): array
    {
        return $this->db
            ->setResponseMode( 'array' )
            ->select(
                'blocks',
                [],
                [ 'block_name' => [ $block_name, 'string' ] ]
            );

    }
    
    public function getBlockDataById( $block_id ): array
    {
        return $this->db
            ->setResponseMode( 'array' )
            ->select(
                'blocks',
                [],
                [ 'block_id' => [ $block_id, 'string' ] ]
            );

    }
}