<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
use PHPUnit\Framework\Assert;

trait ResourceLoaderTrait
{
    /**
     * Returns contents of resource file.
     *
     * @param string $resourceName (file path relative to the tests/resources directory)
     */
    private function getResource(string $resourceName): string
    {
        $contents = file_get_contents($this->getPath($resourceName));
        Assert::assertNotFalse($contents);

        return $contents;
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

    private function getFileName(string $resourceName): BaseLineFileName
    {
        return new BaseLineFileName($this->getPath($resourceName));
    }
}
