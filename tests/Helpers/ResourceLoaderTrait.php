<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers;

trait ResourceLoaderTrait
{
    /**
     * Returns contents of resource file.
     *
     * @param string $resourceName (file path relative to the tests/resources directory)
     */
    private function getResource(string $resourceName): string
    {
        return file_get_contents($this->getPath($resourceName));
    }

    /**
     * Returns path of resource.
     *
     * @param string $resourceName (file path relative to the tests/resources directory)
     */
    private function getPath(string $resourceName): string
    {
        return __DIR__.'/../resources/'.$resourceName;
    }
}
