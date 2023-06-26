<?php

namespace Safronik\Elements;

interface DBGatewayElementInterface
{
    public function getElementData( $element_id ): array;
}