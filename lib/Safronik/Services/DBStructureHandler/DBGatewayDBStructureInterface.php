<?php

namespace Safronik\Services\DBStructureHandler;

interface DBGatewayDBStructureInterface
{
    public function isTableExists( $table ): bool;
    public function createTable( $table, $scheme ): bool;
    public function alterTable( $table, $columns_create = [], $columns_change = [], $columns_drop = [], $indexes = [] ): bool;
    public function getTableColumns( $table ): array;
    public function getPrefix(): string;
    public function getAppPrefix(): string;
}