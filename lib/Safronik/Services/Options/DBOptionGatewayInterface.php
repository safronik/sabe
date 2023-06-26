<?php

namespace Safronik\Services\Options;

interface DBOptionGatewayInterface
{
    public function getOptionsByGroup( $group ): array;
    public function loadOption( $option, $group ): mixed;
    public function saveOption( $option, $group, $value ): int;
    public function removeOption( $option, $group ): int;
}