<?php

namespace KamilPietrzkiewicz\Sargit\Php\Database\Schema\Resources;
class DatabaseTableRowResource
{    /**
     * @var string
     */
    private $fieldTitle;

    /**
     * @var string
     */
    private $fieldType;

    public function setFieldTitle(string $fieldTitle)
    {
        $this->fieldTitle = $fieldTitle;
    }

    public function getFieldTitle() : string 
    {
        return $this->fieldTitle;
    }

    public function setFieldType(string $fieldType)
    {
        $this->fieldType = $fieldType;
    }

    public function getFieldType() : string 
    {
        return $this->fieldType;
    }
}
