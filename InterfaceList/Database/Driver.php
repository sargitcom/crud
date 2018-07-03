<?php

namespace KamilPietrzkiewicz\Sargit\Php\Database\Schema\InterfaceList\Database;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Collection\DatabaseSchemaCollection;

interface Driver
{
	public function connectToDatabase(
		string $host, 
		string $port, 
		string $databaseName, 
		string $login, 
		string $password
	) : bool;
	
	public function getSchema() : DatabaseSchemaCollection;
}
