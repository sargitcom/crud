<?php

namespace KamilPietrzkiewicz\Sargit\Php\Database\Schema\Resources;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Collection\DatabaseRowCollection;

class DatabaseTableResource
{
	private $rowsCollection;
	
	private $tableName;
	
	public function __construct(string $tableName, DatabaseRowCollection $rowsCollection)
	{
		$this->tableName = $tableName;

		$this->rowsCollection = $rowsCollection;
	}

	public function getTableName() : string
	{
		return $this->tableName;
	}
	
	public function getTableRows() : DatabaseRowCollection
	{
		return $this->rowsCollection;
	}
}
