<?php

namespace KamilPietrzkiewicz\Sargit\Php\Database\Schema;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\InterfaceList\Database\Driver as DatabaseDriver;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Collection\DatabaseSchemaCollection;

class GetSchema
{
	/**
	 * @var DatabaseDriver
	 */
	private $dbDriver;

	public function __construct(DatabaseDriver $dbDriver)
	{
		$this->dbDriver = $dbDriver;
	}

	public function getSchema() : DatabaseSchemaCollection
	{
		$this->dbDriver->connectToDatabase(
			'localhost', 
			3306,
			'seotulip',
			'root',
			''
		);
		return $this->dbDriver->getSchema();
	}
}
