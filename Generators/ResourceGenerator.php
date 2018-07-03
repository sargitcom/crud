<?php

namespace KamilPietrzkiewicz\Sargit\Php\Database\Schema\Generators;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Resources\DatabaseTableResource;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Collection\DatabaseRowCollection;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Generators\ResourceNameGenerator;

class ResourceGenerator
{
    private $tableName;

    private $tableSchema;

    private $resourceName;

    private $namespace;

    public function __construct(
        ResourceNameGenerator $resourceNameGenerator,
        DatabaseTableResource $tableSchema
    ) {
        $this->tableName = $tableSchema->getTableName();

        $this->tableSchema = $tableSchema->getTableRows();

        $this->resourceName = $resourceNameGenerator->getResourceName();

        $this->namespace = $resourceNameGenerator->getNamespace();
    }

    public function getResource() : string
    {
        $resourceBuffer = "<?php\n\n";

        $resourceBuffer .= "namespace " . $this->namespace . "\\Resources;\n\n";

        $resourceBuffer .= "class " . $this->resourceName . "Resource \n{";

        // generate properties
        $resourceBuffer .= $this->getPropertiesCode($this->tableSchema);

        $resourceBuffer .= "}\n";

        return $resourceBuffer;
    }



    private function getPropertiesCode(DatabaseRowCollection $dbRowCollection) : string
    {
        $resourceBuffer = "";

        while ($dbRowCollection->valid()) {
            $property = $dbRowCollection->current();

            $fieldTitle = $this->getFieldTitle($property->getFieldTitle());

            $resourceBuffer .= $this->getPropertyPHPDoc($property->getFieldType());
            $resourceBuffer .= $this->getPropertyDeclaration($fieldTitle);

            $dbRowCollection->next();
        }

        $dbRowCollection->rewind();

        while ($dbRowCollection->valid()) {
            print_r($dbRowCollection->key());

            $property = $dbRowCollection->current();

            $resourceBuffer .= $this->getMethodsDeclaration($property->getFieldTitle(), $property->getFieldType());

            $dbRowCollection->next();
        }

        return $resourceBuffer;
    }

    private function getFieldTitle(string $dbFieldTitle)
    {
        $fieldTitle = explode('_', $dbFieldTitle);
        $fieldTitle = array_map(function($element){
            return ucfirst($element);
        }, $fieldTitle);
        $fieldTitle[0] = lcfirst($fieldTitle[0]);
        $fieldTitle = implode('', $fieldTitle);

        return $fieldTitle;
    }

    private function getPropertyPHPDoc(string $fieldType) : string
    {
        return "\n\t/**\n\t * @var " . ($fieldType == "DateTime" ? "\\" . $fieldType : $fieldType) . "\n\t */";
    }

    private function getPropertyDeclaration(string $fieldTitle)
    {
        return "\n\tprivate $" . $fieldTitle . ";\n\n";
    }

    private function getMethodsDeclaration(string $fieldName, string $fieldType)
    {
        $fieldName = $this->getFieldTitle($fieldName);

        $code = "";

        $code .= "\tpublic function set" . ucfirst($fieldName) . "(" . ($fieldType == "DateTime" ? "\\" . $fieldType : $fieldType) . " $" . $fieldName . ") \n";
        $code .= "\t{\n";
        $code .= "\t\t\$this->" . $fieldName . " = $" . $fieldName . ";\n";
        $code .= "\t}\n\n";

        $code .= "\tpublic function get" . ucfirst($fieldName) . "() : " . ($fieldType == "DateTime" ? "\\" . $fieldType : $fieldType) . " \n";
        $code .= "\t{\n";
        $code .= "\t\treturn \$this->" . $fieldName . ";\n";
        $code .= "\t}\n\n";

        return $code;
    }
}