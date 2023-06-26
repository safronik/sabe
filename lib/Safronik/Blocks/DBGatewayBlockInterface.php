<?php

namespace Safronik\Blocks;

interface DBGatewayBlockInterface
{
    public function getBlockData( $block_name ): array;
    public function getBlockDataByName( $block_name ): array;
    public function getBlockDataById( $block_id ): array;
}