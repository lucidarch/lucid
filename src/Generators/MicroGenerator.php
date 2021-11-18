<?php

namespace Lucid\Generators;

use DOMXPath;
use DOMDocument;
use Lucid\Generators\Generator as GeneratorAlias;

class MicroGenerator extends GeneratorAlias
{
    use DirectoriesGeneratorTrait;

    /**
     * The directories to be created.
     *
     * @var array
     */
    private $directories = [
        'app' => [
            'Data',
            'Domains',
            'Features',
            'Operations',
            'Data/Models',
        ],
        'tests' => [
            'Domains',
            'Operations',
        ]
    ];

    /**
     * Generate initial directory structure.
     *
     * @return array
     */
    public function generate()
    {
        $created = $this->generateDirectories();

        $created = array_merge($created, $this->generateCustomDirectories());

        $this->updatePHPUnitXML();

        return $created;
    }

    private function updatePHPUnitXML()
    {
        $root = base_path();
        $path = "$root/phpunit.xml";

        if ($this->exists($path)) {
            $xml = new DOMDocument('1.0', 'UTF-8');
            $xml->preserveWhiteSpace = true;
            $xml->formatOutput = true;
            $xml->load($path);

            $fragment = $xml->createDocumentFragment();
            $fragment->appendXML($this->testsuites());

            $xpath = new DOMXPath($xml);

            // replace tests/Feature with tests/Features
            $feature = $xpath->evaluate('//testsuite[@name="Feature"]')->item(0);
            if ($feature) {
                $feature->parentNode->removeChild($feature);
                $xml->save($path);
            }

            $xpath->evaluate('//testsuites')
                ->item(0)
                ->appendChild($fragment);

            $xml->save($path);
        }
    }

    private function testsuites()
    {
        return <<<XMLSUITE
    <testsuite name="Domains">
            <directory suffix="Test.php">./tests/Domains</directory>
        </testsuite>
        <testsuite name="Operations">
            <directory suffix="Test.php">./tests/Operations</directory>
        </testsuite>
        <testsuite name="Features">
            <directory suffix="Test.php">./tests/Features</directory>
        </testsuite>
\t
XMLSUITE;

    }

    /**
     * @return array
     */
    private function generateCustomDirectories()
    {
        $root = base_path();

        $created = [];
        // rename or create tests/Features directory
        if ($this->exists("$root/tests/Feature")) {
            $this->rename("$root/tests/Feature", "$root/tests/Features");
        } else {
            $this->createDirectory("$root/tests/Features");
            $created[] = 'tests/Features';
        }

        return $created;
    }
}
