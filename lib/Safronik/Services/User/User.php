<?php

namespace Safronik\Services\User;

// Interfaces
use Safronik\Core\CodeTemplates\Interfaces\Installable;
use Safronik\Services\Serviceable;

// Templates
use Safronik\Core\CodeTemplates\Service;
use Safronik\Core\CodeTemplates\Installer;

class User implements Serviceable, Installable
{
    use Service, Installer;
    
    protected static string $service_alias = 'user';
    protected static string $gateway_alias = 'user';
    
    protected int    $id = 0;
    protected string $login = '';
    protected string $email = '';
    protected string $role = '';
    protected string $sid = '';
    protected string $user_group = '';
    
    protected DBUserGatewayInterface $gateway;
    
    public function __construct( DBUserGatewayInterface $gateway, $id = null, $login = null, $email = null, $sid = null )
    {
        ( $id || $login || $email || $sid ) && throw new \Exception('No data given to create user');
        
        $this->gateway = $gateway;
        $db_result     = [];
        
        if( $id ){
            $db_result = $this->getUserdataBy( 'id', $id );
        }elseif( $login ){
            $db_result = $this->getUserdataBy( 'login', $login );
        }elseif( $email ){
            $db_result = $this->getUserdataBy( 'email', $email );
        }elseif( $sid ){
            $db_result = $this->getUserdataBy( 'sid', $sid );
        }
        
        $this->setUser( ...$db_result );
    }
    
    private function setUser($user_id = null, $login = null, $email = null, $role = null, $sid = null): void
    {
        $this->id    = $user_id ?? $this->id;
        $this->login = $login   ?? $this->login;
        $this->email = $email   ?? $this->email;
        $this->role  = $role    ?? $this->role;
        $this->sid   = $sid     ?? $this->sid;
    }
    
    private function getUserdataBy( $type, $needle )
    {
        if( ! in_array( $type, ['email', 'id', 'login', 'sid'] ) ){
            throw new \Exception('Wrong data type when getting user');
        }
        
        return $this->gateway->getUserBy( $type, $needle);
    }
    
    public function getById( $id )
    {
         $this->setUser( ...$this->getUserdataBy( 'id', $id ) );
         
         return $this;
    }
    
    public function getByEmail( $email )
    {
         $this->setUser( ...$this->getUserdataBy( 'email', $email ) );
         
         return $this;
    }
    
    public function getByLogin( $login )
    {
        $this->setUser( ...$this->getUserdataBy( 'login', $login ) );
        
        return $this;
    }
    
    public function getBySID( $sid )
    {
         $this->setUser( ...$this->getUserdataBy( 'sid', $sid ) );
         
         return $this;
    }
    
    public function getBy( $type, $needle )
    {
        if( ! in_array( $type, ['email', 'id', 'login', 'sid'] ) ){
            throw new \Exception('Wrong data type when getting user');
        }
        
        $this->setUser( ...$this->getUserdataBy( $type, $needle) );
        
        return $this;
    }
    
    public function isAdmin()
    {
        return $this->user_group === 'admin';
    }
}