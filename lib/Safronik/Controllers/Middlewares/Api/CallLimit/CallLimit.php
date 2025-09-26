<?php

namespace Safronik\Controllers\Middlewares\Api\CallLimit;

use Exception;
use Models\Gateways\CallLimitGateway;
use Safronik\DB\DB;
use Safronik\Middleware\MiddlewareInterface;

class CallLimit implements MiddlewareInterface{

    /**
     * Throws an exception if the limit exceeded in the current period of time
     *
     * @param array $parameters
     *
     * @return void
     * @throws Exception
     */
    public function execute( array $parameters = [] ): void
    {
        (
            new CallLimitModel(
                new CallLimitGateway( DB::getInstance() ),
                $parameters['period'],
                $parameters['limit'],
                $parameters['parameters'],
                $parameters['action']
            )
        )
        ->execute();
    }
}