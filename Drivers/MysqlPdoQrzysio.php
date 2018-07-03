<?php

namespace KamilPietrzkiewicz\Sargit\Php\Database\Schema\Drivers;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\InterfaceList\Database\Driver as DbDriver;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Collection\DatabaseSchemaCollection;
use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Collection\DatabaseRowCollection;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Resources\DatabaseTableRowResource;
use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Resources\DatabaseTableResource;

class MysqlPdoQrzysio implements DbDriver 
{
	/**
	 * @var Db
	 */
	private $dbConnection;
	
	public function __construct()
	{
		require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Configs' . DIRECTORY_SEPARATOR . 'configPdoQrzysio.php');
	}

	public function connectToDatabase(
		string $host, 
		string $port, 
		string $databaseName, 
		string $login, 
		string $password
	) : bool {
		$this->dbConnection = new \Db();
		return true;
	}

	public function getSchema() : DatabaseSchemaCollection
	{
		$sql = 'SHOW TABLES';
		
		$this->dbConnection->query($sql);
		
		$results = $this->dbConnection->fetch();
		
		$dbSchemaCollection = new DatabaseSchemaCollection();
		
		if (empty($results)) {
			return $dbSchemaCollection;
		}
		
		foreach ($results as $result) {
			$tableName = array_pop($result);

			$sql = 'DESC ' . $tableName;
			
			$this->dbConnection->query($sql);
			
			$tableRows = $this->dbConnection->fetch();
			
			$rowsCollection = new DatabaseRowCollection();
			
			foreach ($tableRows as $tableRow) {
				$tableRowResource = new DatabaseTableRowResource();
				
				$tableRowResource->setFieldTitle($tableRow['Field']);
				
				$fieldType = $this->getFieldType($tableRow['Type']);
				
				$tableRowResource->setFieldType($fieldType);
				
				$rowsCollection->append($tableRowResource);
			}
			
			$rowsCollection->rewind();
			
			$dbTable = new DatabaseTableResource($tableName, $rowsCollection);
			
			$dbSchemaCollection->append($dbTable);
		}
		
		$dbSchemaCollection->rewind();
		
		return $dbSchemaCollection;
	}
	
	private function getFieldType($fieldType)
	{
		$matchResult = preg_match('/(int)/', $fieldType);
		
		if ($matchResult) {
			return 'int';
		}
		
		$matchResult = preg_match('/(text)/', $fieldType);
		
		if ($matchResult) {
			return 'string';
		}
		
		$matchResult = preg_match('/(varchar)/', $fieldType);
		
		if ($matchResult) {
			return 'string';
		}
		
		$matchResult = preg_match('/(timestamp)/', $fieldType);
		
		if ($matchResult) {
			return 'DateTime';
		}
		
		return $fieldType;
	}
}