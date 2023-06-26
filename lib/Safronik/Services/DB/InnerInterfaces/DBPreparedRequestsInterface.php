<?php

namespace Safronik\Services\DB\InnerInterfaces;

interface DBPreparedRequestsInterface{
 
	// Accessible only for PDO
	public function createPreparedQuery( $query, $values );
	public function executePreparedQuery();
 
}