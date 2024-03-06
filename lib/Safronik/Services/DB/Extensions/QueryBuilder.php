<?php

namespace Safronik\Services\DB\Extensions;

use Safronik\Services\DB\Extensions\QueryBuilder\Delete;
use Safronik\Services\DB\Extensions\QueryBuilder\Insert;
use Safronik\Services\DB\Extensions\QueryBuilder\Join;
use Safronik\Services\DB\Extensions\QueryBuilder\Select;
use Safronik\Services\DB\Extensions\QueryBuilder\Update;

trait QueryBuilder
{
    /**
     * Dive in the beautiful world of JOIN requests!
     *
     * @param string $table
     *
     * @return Join
     */
    public function join( string $table ): Join
    {
        return new Join( $table );
    }
    
    /**
     * Returns all selected entries
     *
     * @param string $table
     *
     * @return Select
     */
    public function select( string $table ): Select
    {
        return new Select( $table, $this );
    }
    
    
    /**
     * Fires complied insert query
     *
     * @param string $table
     *
     * @return Insert
     * @throws \Exception
     */
    public function insert( string $table ): Insert
    {
        return new Insert( $table, $this );
    }
    
    /**
     * Fires complied insert query
     *
     * @param $table
     *
     * @return Update
     */
    public function update( string $table ): Update
    {
        return new Update( $table, $this );
    }
    
    /**
     * Compile and fires delete request
     *
     * @param string $table
     *
     * @return Delete
     */
    public function delete( string $table ): Delete
    {
        return new Delete( $table, $this );
    }
    
    /**
     * Wrap string into the passed char
     *
     * @param string $string
     * @param string $char
     *
     * @return string
     */
    private function wrap( string $string, string $char = '"' ): string
    {
        return str_pad( $string, strlen( $string ) + 2, $char, STR_PAD_BOTH );
    }
}