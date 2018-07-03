<?php

namespace KamilPietrzkiewicz\Sargit\Php\Database\Schema\Collection;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Resources\DatabaseTableResource;

class DatabaseSchemaCollection extends Collection
{
    public function __construct()
    {
        $this->position = 0;
        $this->total = 0;
    }

    public function current() : DatabaseTableResource
    {
        return $this->array[$this->position];
    }

    public function append(DatabaseTableResource $databaseSchema)
    {
        $this->array[$this->position++] = $databaseSchema;
    }
}
