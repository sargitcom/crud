<?php

namespace KamilPietrzkiewicz\Sargit\Php\Database\Schema\Collection;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Resources\DatabaseTableRowResource;

class DatabaseRowCollection extends Collection
{
    public function current() : DatabaseTableRowResource
    {
        return $this->array[$this->position];
    }

    public function append(DatabaseTableRowResource $databaseSchema)
    {
        $this->array[$this->position++] = $databaseSchema;
    }
}
