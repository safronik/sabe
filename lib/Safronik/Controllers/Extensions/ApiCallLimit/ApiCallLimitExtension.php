<?php

namespace Safronik\Controllers\Extensions\ApiCallLimit;

use Models\Gateways\ApiCallLimitGateway;
use Safronik\DB\DB;

trait ApiCallLimitExtension{
    
    /**
     * Throws an exception if the limit exceeded in the current period of time
     *
     * @param int   $period
     * @param int   $limit
     * @param array $parameters
     *
     * @return void
     * @throws \Exception
     */
    protected function controlLimits( int $period, int $limit, array $parameters, $action = 'throw' ): void
    {
        ( new ApiCallLimitModel(
            new ApiCallLimitGateway( DB::getInstance() ),
            $period,
            $limit,
            $parameters,
            $action
        ) )
            ->execute();
    }
}