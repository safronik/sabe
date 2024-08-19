<?php

namespace Safronik\Controllers\Extensions\ApiCallLimit;

use Extensions\ApiCallLimit\Exceptions\ApiCallLimitException;
use InvalidArgumentException;
use Safronik\Helpers\TimeHelper;

/**
 * Do not store each call time
 */
class ApiCallLimitModel{
    
    private int    $duration;
    private int    $call_limit;
    private array  $parameters;
    private string $action;
    
    private ApiCallLimitGatewayInterface $gateway;
    
    public function __construct( ApiCallLimitGatewayInterface $gateway, int $interval_duration, int $call_limit, array $parameters, string $actions = 'throw' )
    {
        ! in_array( $actions, [ 'throw', 'sleep' ] )
            && throw new InvalidArgumentException( "Exceed action $actions is invalid" );
        
        $this->gateway    = $gateway;
        $this->duration   = $interval_duration;
        $this->call_limit = $call_limit;
        $this->parameters = $parameters;
        $this->action     = $actions;
    }
    
    /**
     * Limit the amount of calls in the period of time
     *
     * Available actions:
     *  - Halt the script if the limit exceeded
     *  - throw exception
     *
     * @return void
     * @throws \Exception
     */
    public function execute(): void
    {
        $interval = $this->gateway->getIntervalById(
            $this->generateIdFromParameters( $this->parameters )
        );
        
        if( $interval ){
            
            // Interval is no longer actual. Remake it.
            // Actually I could just update it, but I'm too lazy right now to do this
            if( $interval->isIntervalPassedDuration( $interval, $this->duration ) ){
                $this->gateway->dropInterval( $interval );
                $this->gateway->updateInterval( (object) [
                    'id'       => $this->generateIdFromParameters( $this->parameters ),
                    'start'    => TimeHelper::getIntervalStart( $this->duration ),
                    'calls'    => 1,
                ] );
                
            // Make a limit exceeded action
            }elseif( $this->isIntervalLimitExceeded( $interval, $this->call_limit ) ){
                $this->makeExceededAction( $interval, 'throw' );
            
            // Increase interval calls
            }else{
                $interval->calls++;
                $this->gateway->updateInterval( $interval );
            }
        }
    }
    
    /**
     * Performs an action. Should be called if the interval exceeded.
     *
     * @param object $interval
     *
     * @return void
     * @throws \Exception
     */
    private function makeExceededAction( object $interval ): void
    {
        switch( $this->action ){
            
            // Sleeps until next peeriod is started. Slows down execution
            case 'sleep':
                time_sleep_until(
                    $interval->start + $this->duration
                );
                break;
            
            // Trows an exception
            case 'throw':
                $next_interval_starts_in = $this->duration - ( time() % $this->duration ) ;
                throw new ApiCallLimitException( 'Call limit exceeded. You can try again in ' . $next_interval_starts_in . ' seconds', 425 );
        }
    }

    /**
     * Generate interval ID from parameters in the way that parameters oder isn't affect the result
     *
     * @param array $parameters
     *
     * @return string
     */
    private function generateIdFromParameters( array $parameters ): string
    {
        ksort( $parameters );
        
        return md5( implode('', array_merge( array_keys( $parameters ), $parameters  ) ) );
    }
    
    public function isIntervalPassed( object $interval, int $duration ): bool
    {
        return time() >= $interval->start + $duration;
    }
    
    public function isIntervalLimitExceeded( object $interval, int $limit ): bool
    {
        return $limit <= $interval->calls;
    }
}