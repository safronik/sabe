<?php

namespace Safronik\Services\DB\InnerInterfaces;

interface DBHelperMethodsInterface
{
    public function isTableExists( $table_name );
    public function dropTable( $table_name );
	public function getTableScheme( $table );
    public function sanitizeValue($value, $type);
    public function createTable( $table, $columns, $indexes = [], $if_not_exist = false ): bool;
    public function alterTable( $table, $columns_create = [], $columns_change = [], $columns_drop = [], $indexes = [] ): bool;
}