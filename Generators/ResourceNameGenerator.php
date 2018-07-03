<?php

namespace KamilPietrzkiewicz\Sargit\Php\Database\Schema\Generators;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Resources\DatabaseTableResource;

class ResourceNameGenerator
{
    private $namespace;

    private $tableName;

    private $tableSchema;

    public function __construct(string $namespace, DatabaseTableResource $tableSchema)
    {
        $this->namespace = $namespace;

        $this->tableName = $tableSchema->getTableName();

        $this->tableSchema = $tableSchema->getTableRows();
    }

    public function getNamespace() : string
    {
        return $this->namespace;
    }

    public function getTableName() : string
    {
        return $this->tableName;
    }

    public function getResourceName() : string
    {
        $resourceName = explode('_', $this->tableName);

        $resourceName = implode('',array_map(function($element){
            return ucfirst($element);
        }, $resourceName));

        return $resourceName;
    }
}
