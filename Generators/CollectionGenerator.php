<?php

namespace KamilPietrzkiewicz\Sargit\Php\Database\Schema\Generators;

use KamilPietrzkiewicz\Sargit\Php\Database\Schema\Generators\ResourceNameGenerator;

class CollectionGenerator
{
    private $resourceName;

    private $namespace;

    public function __construct(
        ResourceNameGenerator $resourceNameGenerator
    ) {
        $this->resourceName = $resourceNameGenerator->getResourceName();

        $this->namespace = $resourceNameGenerator->getNamespace();
    }

    public function getCollection() : string
    {
        $resourceBuffer = "<?php\n\n";

        $resourceBuffer .= "namespace " . $this->namespace . "\\Collections;\n\n";

        $resourceBuffer .= "class " . $this->resourceName . "Collection \n{\n";

        // generate methods
        $resourceBuffer .= $this->getMethodsDeclaration($this->resourceName);

        $resourceBuffer .= "}\n";

        return $resourceBuffer;
    }

    private function getMethodsDeclaration(string $resourceName)
    {
        $code = "";

        $code .= "\tpublic function current() : " . ucfirst($resourceName) . "\n";
        $code .= "\t{\n";
        $code .= "\t\treturn \$this->array[\$this->position];\n";
        $code .= "\t}\n\n";

        $code .= "\tpublic function append(" . ucfirst($resourceName) . " \$" . lcfirst($resourceName) . ")\n";
        $code .= "\t{\n";
        $code .= "\t\t\$this->array[\$this->position++] = \$databaseSchema;\n";
        $code .= "\t}\n\n";

        return $code;
    }
}