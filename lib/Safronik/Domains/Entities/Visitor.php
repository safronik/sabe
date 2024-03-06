<?php

namespace Safronik\Domains\Entities;

class Visitor extends EntityObject{
 
	private string $ip;
    private int    $ip_decimal;
	private string $browser_signature;
	private string $user_agent;
	private int    $hits;
    
    public static array $rules = [
        'id'                 => [ 'type' => 'string',  'required' => true, ],
        'ip'                 => [ 'type' => 'string',  'required' => true, ],
        'ip_decimal'         => [ 'type' => 'integer', 'required' => true, ],
        'browser_signature'  => [ 'type' => 'string',  'required' => true, ],
        'user_agent'         => [ 'type' => 'string',  'required' => true, ],
        'hits'               => [ 'type' => 'integer', 'required' => true, ],
    ];
    
    public function __construct( $id, $ip, $ip_decimal, $browser_signature, $user_agent, $hits = 1 )
    {
		$this->id                = $id ?: hash( 'sha256', $ip . $browser_signature . $user_agent );
		$this->ip                = $ip;
        $this->ip_decimal        = $ip_decimal ?: ip2long( $this->ip ) ?: 0;
		$this->browser_signature = $browser_signature;
		$this->user_agent        = $user_agent;
		$this->hits              = $hits;
    }
    
    public function getIp(): string
    {
        return $this->ip;
    }
    
    public function getIpDecimal(): int
    {
        return $this->ip_decimal;
    }
    
    public function getBrowserSignature(): string
    {
        return $this->browser_signature;
    }
    
    public function getUserAgent(): string
    {
        return $this->user_agent;
    }
    
    public function getHits(): int
    {
        return $this->hits;
    }
}