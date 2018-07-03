<?php

namespace KamilPietrzkiewicz\Sargit\Php\Database\Schema\Collection;

use \Iterator;

abstract class Collection implements Iterator
{
    protected $position = 0;
    protected $array = [];
    protected $total = 0;

    public function __construct()
    {
        $this->position = 0;
        $this->total = 0;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->array[$this->position]);
    }

    public function count()
    {
        return count($this->array);
    }

    public function setTotal(int $total)
    {
        $this->total = $total;
    }

    public function getTotal() : int
    {
        return $this->total;
    }
}
