<?php

namespace Safronik\Services\DB\InnerInterfaces;

interface DBFluidAccess
{
    // Fluid interface access
    public function setTable( string $table ): DBPreparedRequestsInterface;
	public function setColumns( array|string $columns, array|string $values ): DBPreparedRequestsInterface;
	public function setReplacement( array $values ): DBPreparedRequestsInterface;
	public function setSortOrder( string $order ): DBPreparedRequestsInterface;
	public function setOperation( string $operation ): DBPreparedRequestsInterface;
	public function setWhere( array|string $columns, array|string $equation, array|string $values ): DBPreparedRequestsInterface;
	public function setLimit( int $start, int $amount ): DBPreparedRequestsInterface;
}