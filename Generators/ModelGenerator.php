<?php

namespace KamilPietrzkiewicz\Sargit\Php\Database\Schema\Generators;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Generators\ResourceNameGenerator;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Resources\DatabaseTableResource;
use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Collection\DatabaseRowCollection;
use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Resources\DatabaseTableRowResource;

class ModelGenerator
{
    const WITH_COLON = true;
    const WITHOUT_COLON = false;
    const DO_NOT_SKIP_PRIMARY_COLUMN = false;

    private $resourceName;

    private $namespace;

    private $tableName;

    private $tableSchema;

    public function __construct(
        ResourceNameGenerator $resourceNameGenerator,
        DatabaseTableResource $tableSchema
    ) {
        $this->resourceName = $resourceNameGenerator->getResourceName();

        $this->namespace = $resourceNameGenerator->getNamespace();

        $this->tableName = $resourceNameGenerator->getTableName();

        $this->tableSchema = $tableSchema;
    }

    /**
     * @return string
     */
    public function getModel() : string
    {
        $resourceBuffer = "<?php\n\n";

        $resourceBuffer .= "namespace " . $this->namespace . "\\Models;\n\n";

        $resourceBuffer .= $this->getEntitiesDeclaration($this->resourceName);

        $resourceBuffer .= "class " . $this->resourceName . "Model \n{\n";

        $resourceBuffer .= "\tconst NO_LIMIT = 0;\n\n";

            // generate create method
        $resourceBuffer .= $this->getCreateMethodDeclaration(
            $this->tableName,
            $this->resourceName,
            $this->tableSchema
        );

        // generate read method
        $resourceBuffer .= $this->getReadCollectionDeclaration(
            $this->tableName,
            $this->resourceName,
            $this->tableSchema
        );

        $resourceBuffer .= $this->getReadByIdDeclaration(
            $this->tableName,
            $this->resourceName,
            $this->tableSchema
        );

        $resourceBuffer .= $this->getReadsHelperDeclaration(
            $this->resourceName,
            $this->tableSchema
        );

        $resourceBuffer .= $this->getUpdateByCollectionDeclaration(
            $this->tableName,
            $this->resourceName,
            $this->tableSchema
        );

        $resourceBuffer .= $this->getUpdateDataInsertDataIntoTempDbTableDeclaration(
            $this->tableName,
            $this->resourceName,
            $this->tableSchema
        );

        $resourceBuffer .= $this->getUpdateDataBindParamDeclaration(
            $this->tableName,
            $this->resourceName,
            $this->tableSchema
        );

        $resourceBuffer .= $this->getUpdateResourceByResourceDeclaration(
            $this->tableName,
            $this->resourceName,
            $this->tableSchema
        );

        $resourceBuffer .= $this->getDeleteResourceByResourceIdDeclaration(
            $this->tableName,
            $this->resourceName,
            $this->tableSchema
        );

        $resourceBuffer .= "}\n";

        return $resourceBuffer;
    }

    private function getEntitiesDeclaration(string $resourceName) : string
    {
        $code = "";

        $code .= "use " . $this->namespace . "\\Resources\\" . ucfirst($resourceName) . "Resource;\n\n";
        $code .= "use " . $this->namespace . "\\Collections\\" . ucfirst($resourceName) . "Collection;\n\n";

        return $code;
    }

    private function getCreateMethodDeclaration(
        string $tableName,
        string $resourceName,
        DatabaseTableResource $dbTableCollection
    ) : string {
        $code = "";

        $code .= "\tpublic function create" . ucfirst($resourceName) . "Resource(";
        $code .= ucfirst($resourceName) . "Resource \$" . lcfirst($resourceName);
        $code .= ") : int\n";

        $code .= "\t{\n";
        $code .= "\t\t\$sql = \"INSERT INTO " . $tableName . "(";

        $tableRows = $dbTableCollection->getTableRows();

        $code .= $this->getTableColumnsDeclaration($tableRows, self::WITHOUT_COLON);

        $code .= ")\";\n";

        $code .= "\t\t\$sql .= \" VALUES(";

        $code .= $this->getTableColumnsDeclaration($tableRows, self::WITH_COLON);

        $code .= ")\";\n\n";

        $code .= "\t\t\$this->db->query(\$sql);\n";

        $code .= $this->getFieldsDeclaration($tableRows);

        $code .= "\n\t\t\$this->exec();\n\n";

        $code .= "\t\treturn \$this->db->lastInsertId();";

        $code .= "\t}\n\n";

        return $code;
    }

    private function getTableColumnsDeclaration(
        DatabaseRowCollection $dbRows,
        bool $withColon = false,
        bool $skipPrimaryColumn = true,
        string $appendVariableStringNameToVariablesDecleration = ""
    ) : string {
        $code = "";

        $isFirstAndIsKey = true;
        $wasFieldPrinted = false;

        $dbRows->rewind();

        while ($dbRows->valid()) {
            if ($skipPrimaryColumn && $isFirstAndIsKey) {
                $isFirstAndIsKey = false;
                continue;
            }

            $currentRow = $dbRows->current();

            if ($wasFieldPrinted) {
                $code .= ", ";
            }

            $code .= ($withColon ? ":" : "") . $currentRow->getFieldTitle();

            if ($appendVariableStringNameToVariablesDecleration != "") {
                $code .= "_\" . " . $appendVariableStringNameToVariablesDecleration . " . \"";
            }

            if (!$wasFieldPrinted) {
                $wasFieldPrinted = true;
            }

            $dbRows->next();
        }

        return $code;
    }

    private function getFieldsDeclaration(DatabaseRowCollection $dbRows)
    {
        $code = "";

        $dbRows->rewind();

        while ($dbRows->valid()) {

            $dbRow = $dbRows->current();

            $code .= $this->getFieldDeclaration($dbRow);

            //$this->db->bind(':user_id', $userId, PDO::PARAM_STR);

            $dbRows->next();
        }

        return $code;
    }

    private function getFieldDeclaration(DatabaseTableRowResource $dbRow)
    {
        $code = "";

        print_r($dbRow->getFieldType());

        $fieldType = $this->getPdoParamType($dbRow->getFieldType());

        $fieldTitle = $this->getFieldTitle($dbRow->getFieldTitle());

        $code .= "\t\t\$this->db->bind(':" . $dbRow->getFieldTitle() . "', \$" . lcfirst($this->resourceName) . "->get" . $fieldTitle . "()";

        if ($dbRow->getFieldType() === 'DateTime') {
            $code .= "->getTimestamp()";
        }

        $code .= ", " . $fieldType . ");\n";

        return $code;
    }

    private function getPdoParamType(string $phpType)
    {
        if ($phpType == "string" || $phpType == "DateTime") {
            return "PDO::PARAM_STR";
        }

        if ($phpType == "bool") {
            return "PDO::PARAM_BOOL";
        }

        if ($phpType == "int") {
            return "PDO::PARAM_INT";
        }
    }

    private function getFieldTitle(string $fieldTitle) : string
    {
        $fieldTitle = explode("_", $fieldTitle);
        $fieldTitle = array_map(function($element) {
            return ucfirst($element);
        }, $fieldTitle);
        $fieldTitle = implode('', $fieldTitle);

        return $fieldTitle;
    }

    private function getReadCollectionDeclaration(
        string $tableName,
        string $resourceName,
        DatabaseTableResource $dbTableCollection
    ) : string {
        $code = "";

        $code .= "\tpublic function read" . ucfirst($resourceName) . "Collection(int \$page, int \$pageLimit";
        $code .= ") : " . ucfirst($resourceName) . "Collection\n";

        $code .= "\t{\n";
        $code .= "\t\t\$sql = \"SELECT " . $this->getTableFieldsToReadDeclaration($dbTableCollection->getTableRows()) . " FROM " . $tableName . "\";\n\n";

        $code .= "\t\t\$count = \"SELECT count(*) as records_count FROM " . $tableName . "\";\n\n";

        $code .= "\t\tif(\$pageLimit == 0) {\n";
        $code .= "\t\t\t\$currentRow = (\$page - 1) * \$pageLimit;\n";
        $code .= "\t\t\t\$sql .= \" LIMIT \" . \$currentRow;\n";
        $code .= "\t\t}\n\n";

        $code .= "\t\tif(\$pageLimit > 0) {\n";
        $code .= "\t\t\t\$currentRow = (\$page - 1) * \$pageLimit;\n";
        $code .= "\t\t\t\$sql .= \" LIMIT \" . \$currentRow .  \", \" . \$pageLimit;\n";
        $code .= "\t\t}\n\n";

        $code .= "\t\t\$this->db->query(\$sql);\n";
        $code .= "\t\t\$results = \$this->fetch();\n\n";

        $code .= "\t\t\$this->db->query(\$count);\n";
        $code .= "\t\t\$countResult = \$this->fetchOne();\n\n";

        $code .= "\t\t\$" . lcfirst($resourceName) . "Collection = new " . ucfirst($resourceName) . "Collection();\n\n";

        $code .= "\t\tif (empty(\$results)) {\n";
        $code .= "\t\t\treturn \$" . lcfirst($resourceName) . "Collection;\n";
        $code .= "\t\t}\n\n";

        $resourceRecord = lcfirst($resourceName) . "Record";
        
        $code .= "\t\tforeach (\$results as \$" . $resourceRecord . ") {\n";
        
        $code .= "\t\t\t\$" . lcfirst($resourceName) . "Resource = ";

        $code .= "\$this->mapDbRowTo" . $resourceName . "Resource(\$" . $resourceRecord . ");\n";

        $code .= "\n\t\t\t\$" . lcfirst($resourceName) . "Collection->append(\$" . lcfirst($resourceName) . "Resource);\n";
        $code .= "\t\t}\n\n";
        $code .= "\t\t\$" . lcfirst($resourceName) . "Collection->rewind();\n\n";
        $code .= "\t\t\$" . lcfirst($resourceName) . "Collection->setTotal(\$countResult['records_count']);\n\n";
        $code .= "\t\treturn \$" . lcfirst($resourceName) . "Collection;\n";
        $code .= "\t}\n\n";

        return $code;
    }

    private function getTableFieldsToReadDeclaration(DatabaseRowCollection $dbTableRowCollection) : string
    {
        $code = "";

        $isFirstField = true;

        $dbTableRowCollection->rewind();

        while ($dbTableRowCollection->valid()) {

            $dbTableRow = $dbTableRowCollection->current();

            if (!$isFirstField) {
                $code .= ", ";
            }

            $isFirstField = false;

            $code .= $dbTableRow->getFieldTitle();

            $dbTableRowCollection->next();
        }

        return $code;
    }

    private function getReadByIdDeclaration(
        string $tableName,
        string $resourceName,
        DatabaseTableResource $dbTableCollection
    ) : string {
        $code = "";

        $code .= "\tpublic function read" . ucfirst($resourceName) . "ById(int ";

        $dbTableRow = $dbTableCollection->getTableRows();
        $dbTableRow->rewind();

        $keyFieldDbColumnTitle = $dbTableRow->current()->getFieldTitle();

        $code .= "\$" .  lcfirst($this->getFieldTitle($keyFieldDbColumnTitle));

        $code .= ") : " . ucfirst($resourceName) . "Resource\n";

        $code .= "\t{\n";
        $code .= "\t\t\$sql = \"SELECT " . $this->getTableFieldsToReadDeclaration($dbTableCollection->getTableRows()) . " FROM " . $tableName;
        $code .= " WHERE " . $keyFieldDbColumnTitle . " = :" . $keyFieldDbColumnTitle . "\";\n\n";

        $code .= "\t\t\$this->db->query(\$sql);\n\n";

        $code .= "\t\t\$this->db->bind(':" . $keyFieldDbColumnTitle . "', \$" . lcfirst($this->getFieldTitle($keyFieldDbColumnTitle)) . ", PDO::PARAM_INT);\n\n";

        $code .= "\t\t\$result = \$this->fetchOne();\n\n";

        $code .= "\t\t\$" . lcfirst($resourceName) . "Collection = new " . ucfirst($resourceName) . "Collection();\n\n";

        $code .= "\t\tif (empty(\$result)) {\n";
        $code .= "\t\t\treturn \$" . lcfirst($resourceName) . "Collection;\n";
        $code .= "\t\t}\n\n";

        $code .= "\t\t\$" . lcfirst($resourceName) . "Resource = \$this->mapDbRowTo" . $resourceName . "Resource(\$result);\n";
        $code .= "\t\t\$" . lcfirst($resourceName) . "Collection->append(\$" . lcfirst($resourceName) . "Resource);\n\n";
        $code .= "\t\t\$" . lcfirst($resourceName) . "Collection->rewind();\n\n";
        $code .= "\t\t\$" . lcfirst($resourceName) . "Collection->setTotal(1);\n\n";
        $code .= "\t\treturn \$" . lcfirst($resourceName) . "Collection;\n";
        $code .= "\t}\n\n";

        return $code;
    }

    private function getReadsHelperDeclaration(
        string $resourceName,
        DatabaseTableResource $dbTableCollection
    ) : string {
        $code = "";

        $code .= "\tprivate function mapDbRowTo" . $resourceName . "Resource(array \$dbRecord) : ". $resourceName . "Resource\n";
        $code .= "\t{\n";
        $code .= "\t\t\$" . lcfirst($resourceName) . "Resource = new " . $resourceName . "Resource();\n";

        $dbTableRows = $dbTableCollection->getTableRows();

        $resourceRecord = lcfirst($resourceName) . "Record";

        $code .= $this->getRecordSettersDeclaration($resourceName,  $resourceRecord, $dbTableRows);

        $code .= "\t\treturn \$" . lcfirst($resourceName) . "Resource;\n";
        $code .= "\t}\n\n";

        return $code;
    }

    private function getRecordSettersDeclaration(
        string $resourceName,
        string $resourceRecordName,
        DatabaseRowCollection $dbTableRows
    ) : string {
        $code = "";

        $dbTableRows->rewind();

        while ($dbTableRows->valid()) {
            $tableRow = $dbTableRows->current();

            $code .= "\t\t\$" . lcfirst($resourceName) . "Resource->set" . $this->getFieldTitle($tableRow->getFieldTitle()) . "(";

            $isDateTime = false;

            if ($tableRow->getFieldType() == "DateTime") {
                $isDateTime = true;
                $code .= "new \\DateTime(";
            }

            $code .= "\$" . $resourceRecordName  . "['" . $tableRow->getFieldTitle() . "']";

            if ($isDateTime) {
                $code .= ")";
            }

            $code .= ");\n";

            $dbTableRows->next();
        }

        return $code;
    }

    private function getUpdateByCollectionDeclaration(
        string $tableName,
        string $resourceName,
        DatabaseTableResource $dbTableCollection
    ) : string {
        $code = "";

        $code .= "\tpublic function update" . ucfirst($resourceName) . "CollectionByCollection(" . ucfirst($resourceName) . "Collection \$" . lcfirst($resourceName) . ")\n";

        $code .= "\t{\n";

        $code .= "\t\t\$sqlTempTable = \"CREATE OR REPLACE TEMPORARY TABLE data_update AS SELECT * FROM ". $tableName . " WHERE 1=0\";\n\n";

        $code .= "\t\t\$this->db->query(\$sqlTempTable);\n\n";

        $code .= "\t\t\$this->insertTemp" . ucfirst($resourceName) . "DataIntoTempDbTable(\$" . lcfirst($resourceName) . ");\n\n";

        $dbTableColumns = $dbTableCollection->getTableRows();
        $dbTableColumns->rewind();
        $firstPrimaryColumn = $dbTableColumns->current();
        $firstPrimaryColumnName = $firstPrimaryColumn->getFieldTitle();
        $code .= "\t\t\$sql = \"UPDATE " . $tableName . " JOIN data_update ON " . $tableName . "." . $firstPrimaryColumnName . " = data_update." . $firstPrimaryColumnName . " SET \"";

        $dbTableColumns->rewind();
        $loopCounter = 0;
        while ($dbTableColumns->valid()) {
            $dbTableColumn = $dbTableColumns->current();

            if ($loopCounter === 0) {
                $loopCounter++;
                $dbTableColumns->next();
                continue;
            }

            $loopCounter++;

            $code .= "\n\t\t\t.\"" . ($loopCounter > 2 ? ", " : "") . $tableName . "." . $dbTableColumn->getFieldTitle() . " = data_update." . $dbTableColumn->getFieldTitle() . "\"";

            $dbTableColumns->next();
        }

        $code .= ";\n\n";

        $code .= "\t\t\$this->db->query(\$sql);\n\n";

        $code .= "\t\t\$this->db->exec();\n";

        $code .= "\t}\n\n";

        return $code;
    }

    public function getUpdateDataInsertDataIntoTempDbTableDeclaration(
        string $tableName,
        string $resourceName,
        DatabaseTableResource $dbTableCollection
    ) : string {
        $code = "";

        $code .= "\tprivate function insertTemp" . ucfirst($resourceName) . "DataIntoTempDbTable(";
        $code .= ucfirst($resourceName) . "Collection \$" . lcfirst($resourceName);
        $code .= ")\n";

        $code .= "\t{\n";

        $code .= "\t\t\$" . lcfirst($resourceName) . "->rewind();\n\n";

        $code .= "\t\t\$sql = \"\";\n\n";

        $code .= "\t\t\$countResources = 0;\n\n";

        $code .= "\t\t\$insertData = [];\n\n";

        $code .= "\t\twhile (\$" . lcfirst($resourceName) . "->valid()) {\n";
        $code .= "\t\t\t\$currentRecord = \$" . lcfirst($resourceName) . "->current();\n\n";

        $code .= "\t\t\t\$countResources++;\n\n";

        $code .= "\t\t\tif (\$countResources == 10) {\n";
        $code .= "\t\t\t\t\$countResources = 0;\n\n";
        $code .= "\t\t\t\t\$this->db->query(\$sql);\n\n";

        $code .= "\t\t\t\t\$this->getBindParamsDeclarationForUpdate(\$insertData);\n\n";

        $code .= "\t\t\t\t\$this->db->exec();\n\n";

        $code .= "\t\t\t\t\$insertData = [];\n\n";

        $code .= "\t\t\t\t\$sql = \"\";\n";
        $code .= "\t\t\t}\n\n";

        $code .= "\t\t\t\$sql .= \"INSERT INTO data_update(";

        $dbTableColumns = $dbTableCollection->getTableRows();
        $dbTableColumns->rewind();

        $code .= $this->getTableColumnsDeclaration(
            $dbTableColumns,
            self::WITHOUT_COLON,
            self::DO_NOT_SKIP_PRIMARY_COLUMN
        );

        $code .= ") VALUES(";

        $code .= $this->getTableColumnsDeclaration(
            $dbTableColumns,
            self::WITH_COLON,
            self::DO_NOT_SKIP_PRIMARY_COLUMN,
            '$countResources'
        );

        $code .= "); \";\n\n";

        $code .= "\t\t\t\$insertData[\$countResources] = [\n";
        $code .= $this->declareInsertDataParams($resourceName, $dbTableColumns);
        $code .= "\t\t\t];\n\n";

        $code .= "\t\t\t\$" . lcfirst($resourceName) . "->next();\n\n";

        $code .= "\t\t}\n\n";

        $code .= "\t\tif (\$sql != \"\") {\n";

        $code .= "\t\t\t\$this->db->query(\$sql);\n\n";

        $code .= "\t\t\t\$this->getBindParamsDeclarationForUpdate(\$insertData);\n\n";

        $code .= "\t\t\t\$this->db->exec();\n";

        $code .= "\t\t}\n";

        $code .= "\t}\n\n";

        return $code;
    }

    public function getUpdateDataBindParamDeclaration(
        string $tableName,
        string $resourceName,
        DatabaseTableResource $dbTableCollection
    ) : string {
        $code = "";

        $code .= "\tprivate function getBindParamsDeclarationForUpdate" . ucfirst($resourceName) . "Collection(array $" . lcfirst($resourceName) . "CollectionContainer)\n";
        $code .= "\t{\n";

        $code .= "\t\tforeach (\$" . lcfirst($resourceName) . "CollectionContainer as \$index => \$values) {\n";

            $dbTableColumns = $dbTableCollection->getTableRows();

        $dbTableColumns->rewind();

        while ($dbTableColumns->valid()) {
            $dbColumn = $dbTableColumns->current();

            $code .= "\t\t\t\$this->db->bind(\":" . $dbColumn->getFieldTitle() . "_\" . \$index, \$values['" . $dbColumn->getFieldTitle() . "'], ";

            $code .= $this->getPDOTypeBasedOnVariableType($dbColumn->getFieldType());

            $code .= ");\n";

            $dbTableColumns->next();
        }

        $code .= "\t\t}\n";

        $code .= "\t}\n\n";
        
        return $code;
    }

    private function declareInsertDataParams(
        string $resourceName,
        DatabaseRowCollection $dbTableColumns
    ): string {
        $code = "";

        $dbTableColumns->rewind();
        while ($dbTableColumns->valid()) {
            $dbTableColumn = $dbTableColumns->current();

            $code .= "\t\t\t\t'" . lcfirst($dbTableColumn->getFieldTitle()) . "' => \$currentRecord->get" . $this->getResourceGetterDeclaration($dbTableColumn->getFieldTitle()) . "(),";

            $code .= "\n";

            $dbTableColumns->next();
        }

        return $code;
    }

    private function getResourceGetterDeclaration(string $fieldTitle)
    {
        $fieldTitle = explode("_", $fieldTitle);
        $fieldTitle = implode('', array_map(function($element) {
            return ucfirst($element);
        }, $fieldTitle));
        return $fieldTitle;
    }

    public function getUpdateResourceByResourceDeclaration(
        string $tableName,
        string $resourceName,
        DatabaseTableResource $dbTableCollection
    ) : string {
        $code = "\tpublic function update" . ucfirst($resourceName) . "ResourceByResource(";

        $code .= ucfirst($resourceName) . "Resource \$" . lcfirst($resourceName) . ")\n";

        $code .= "\t{\n";

        $code .= "\t\t\$sql = \"UPDATE " . lcfirst($tableName) . " SET \"\n";

        $dbTableColumns = $dbTableCollection->getTableRows();
        $dbTableColumns->rewind();

        $elementCount = 0;
        $firstColumnName = "";

        while ($dbTableColumns->valid()) {
            $dbTableColumn = $dbTableColumns->current();

            if (!$elementCount) {
                $dbTableColumns->next();
                $firstColumnName = $dbTableColumn->getFieldTitle();
                $elementCount++;
                continue;
            }

            $code .= "\t\t\t. \"" . ($elementCount > 1 ? " ," : "") . $dbTableColumn->getFieldTitle() . " = : " . $dbTableColumn->getFieldTitle() . "\"\n";

            $elementCount++;

            $dbTableColumns->next();
        }

        $code .= "\t\t\t\" WHERE " . lcfirst($tableName) . "." . $firstColumnName . " = :" . $firstColumnName . "\";\n\n";

        $code .= "\t\t\$this->db->query(\$sql);\n\n";

        $dbTableColumns->rewind();
        while ($dbTableColumns->valid()) {
            $dbTableColumn = $dbTableColumns->current();

            $code .= "\t\t\$this->db->bind(':" . $dbTableColumn->getFieldTitle() . "', \$" . lcfirst($resourceName) . "->get" . $this->getGetFieldMethodName($dbTableColumn->getFieldTitle()) . "(), " . $this->getPDOTypeBasedOnVariableType($dbTableColumn->getFieldType()) . ");\n";

            $dbTableColumns->next();
        }

        $code .= "\t\t\$this->db->exec();\n";

        $code .= "\t}\n\n";

        return $code;
    }

    private function getGetFieldMethodName(string $fieldTitle)
    {
        $fieldTitle = explode("_", $fieldTitle);
        $fieldTitle = implode('', array_map(function($element) {
            return ucfirst($element);
        }, $fieldTitle));
        return $fieldTitle;
    }

    private function getPDOTypeBasedOnVariableType(string $type)
    {
        if ($type === 'int') {
            return 'PDO::PARAM_INT';
        }

        return 'PDO::PARAM_STRING';
    }

    public function getDeleteResourceByResourceIdDeclaration(
        string $tableName,
        string $resourceName,
        DatabaseTableResource $dbTableCollection
    ) : string {
        $code = "";

        $code .= "\tpublic function delete" . $resourceName . "ResourceById(";
        $code .= ucfirst($resourceName) . "Resource \$" . lcfirst($resourceName);
        $code .= ")\n";
        $code .= "\t{\n";

        $firstColumnInTable = $dbTableCollection->getTableRows();
        $firstColumnInTable->rewind();
        $firstColumnName = $firstColumnInTable->current()->getFieldTitle();

        $code .= "\t\t\$sql = \"delete " . $tableName . " where " . $firstColumnName . " = :" . $firstColumnName . "\";\n\n";

        $code .= "\t\t\$this->db->query(\$sql);\n\n";

        $code .= "\t\t\$this->db->bind(':" . $firstColumnName . "', ";

        $code .= "\$" . lcfirst($resourceName) . "->get" . $this->getGetFieldMethodName($firstColumnName) . "()";

        $code .= ");\n";

        $code .= "\t\t\$this->db->exec();\n";

        $code .= "\t}\n";
        return $code;
    }
}
