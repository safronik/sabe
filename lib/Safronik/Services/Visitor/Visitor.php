<?php

namespace Safronik\Services\Visitor;

// Interfaces
use Safronik\Core\Variables\Server;

// Templates

// Applied

/**
 * @method registerVisitor( false|string $id, string $ip, false|int|string $ip_decimal, string $user_agent, string $browser_signature )
 * @method addIP()
 * @method addBrowserSignature()
 */
class Visitor{
    
	public string $id;
	public string $ip;
	public string $browser_signature;
	public string $user_agent;
    public int    $ip_decimal;
    private DBVisitorGatewayInterface $gateway;
    
    protected function __construct( DBVisitorGatewayInterface $gateway )
    {
		$this->ip                = $this->addIP();
		$this->ip_decimal        = ip2long( $this->ip ) !== false ? ip2long( $this->ip ) : '0.0.0.0';
		$this->browser_signature = $this->addBrowserSignature();
		$this->user_agent        = $this->addUserAgent();
		$this->id                = hash( 'sha256', $this->ip . $this->browser_signature . $this->user_agent );
        $this->gateway           = $gateway;
		
		$this->registerVisitor(
            $this->id,
            $this->ip,
            $this->ip_decimal,
            $this->user_agent,
            $this->browser_signature
        );
    }
 
    protected function _registerVisitor( ...$visitor_properties )
    {
        $this->gateway->registerVisitor( ...$visitor_properties );
    }
    
	protected function _addIP()
	{
		if( ! isset( $this->ip ) ){
			$this->ip = IP::get();
		}
		
		return $this->ip;
	}
	
	private function _addBrowserSignature()
	{
		if( ! isset( $this->browser_signature ) ){
			// @todo implement
			$this->browser_signature = '';
		}
		
		return $this->browser_signature;
	}
	
	protected function addUserAgent()
	{
		return Server::get('HTTP_USER_AGENT');
	}
	
	public function getId()
	{
		return $this->id;
	}
}