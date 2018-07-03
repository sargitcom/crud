<?php

/**
 *  ANDAW
 */

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'autoload.php');

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Drivers\MysqlPdoQrzysio as DbDriver;
use KamilPietrzkiewicz\Sargit\Php\Database\Schema\GetSchema as GetDbSchema;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Generators\ResourceNameGenerator;
use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Generators\ResourceGenerator;
use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Generators\CollectionGenerator;
use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Generators\ModelGenerator;

try {
    $dbDriver = new DbDriver();

    $dbSchema = new GetDbSchema($dbDriver);
    $dbSchema = $dbSchema->getSchema();

    $resourcesDir = __DIR__ . DIRECTORY_SEPARATOR . 'entities' . DIRECTORY_SEPARATOR . 'resources';
    $collectionsDir = __DIR__ . DIRECTORY_SEPARATOR . 'entities' . DIRECTORY_SEPARATOR . 'collections';
    $modelsDir = __DIR__ . DIRECTORY_SEPARATOR . 'entities' . DIRECTORY_SEPARATOR . 'models';

    $namespace = "KamilPietrzkiewicz\\Software\Web\\SeoTulip";

    $resourceNamespace = $namespace . "\\Resources";
    $resourceNamespace = $namespace . "\\Resources";

    while ($dbSchema->valid()) {
        $tableSchema = $dbSchema->current();

        $resourceNameGenerator = new ResourceNameGenerator($namespace, $tableSchema);
        $resourceName = $resourceNameGenerator->getResourceName();

        $resourceFilePath = $resourcesDir . DIRECTORY_SEPARATOR . $resourceName . "Resource.php";
        $collectionFilePath = $collectionsDir . DIRECTORY_SEPARATOR . $resourceName . "Collection.php";
        $modelFilePath = $modelsDir . DIRECTORY_SEPARATOR . $resourceName . ".php";

        // create resource
        $resourceGenerator = new ResourceGenerator($resourceNameGenerator, $tableSchema);
        $resource = $resourceGenerator->getResource();

        // create collection
        $collectionGenerator = new CollectionGenerator($resourceNameGenerator);
        $collection = $collectionGenerator->getCollection();

        // create model
        $modelGenerator = new ModelGenerator($resourceNameGenerator, $tableSchema);
        $model = $modelGenerator->getModel();

        // create files
        $resourceHandle = fopen($resourceFilePath, 'w+');
        fwrite($resourceHandle, $resource);
        fclose($resourceHandle);

        $collectionHandle = fopen($collectionFilePath, 'w+');
        fwrite($collectionHandle, $collection);
        fclose($collectionHandle);

        $modelHandle = fopen($modelFilePath, 'w+');
        fwrite($modelHandle, $model);
        fclose($modelHandle);

        $dbSchema->next();
    }
} catch(\Exception $e) {
    print_r($e);
}
