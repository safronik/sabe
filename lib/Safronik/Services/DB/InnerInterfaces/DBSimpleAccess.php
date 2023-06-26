<?php

namespace Safronik\Services\DB\InnerInterfaces;

interface DBSimpleAccess
{
    // Simple wrappers for common request
	public function select( $table, $columns, $where );
	public function update( $table, $values, $where );
	public function insert( $table, $values );
	public function delete( $table, $where );
    public function fetch();
	public function fetchAll();
}