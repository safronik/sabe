<?php

namespace Safronik\Services\Options;

use Safronik\Core\Data\Storage;

final class Option extends OptionAbstract{
    
    private DBOptionGatewayInterface $gateway;
    
    public function __construct( DBOptionGatewayInterface $gateway, string $name, string $group = '' )
    {
        $this->gateway = $gateway;
        
        parent::__construct( $name, $group );
    }
    
    public function getDefaults(): array
	{
		return $this->default ?? [];
	}
    
    /**
     * Save option to the DB or other storage
     *
     * @return bool
     */
	public function save(): bool
    {
        return $this->gateway->saveOption( $this->name, $this->group, $this->storage ) === 1;
	}
    
    /**
     * @param        $option string
     * @param string $group
     *
     * @return array|null
     */
	protected function load( string $option, string $group = 'common' ): mixed
    {
        return $this->gateway->loadOption( $option, $group );
	}

	/**
	 * Permanent delete the current option from the storage
	 *
	 * @return bool
	 */
	public function remove(): bool
	{
        return $this->gateway->removeOption( $this->name, $this->group );
	}
    
    public function set( $data ): void
    {
        $data          = json_decode( $data, true ) ?? $data;
        $this->storage = is_scalar( $data )
            ? $data
            : new Storage( $data, true );
    }
}