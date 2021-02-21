<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Integration;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;

trait TestDirectoryTrait
{
    private function createTestDirectory(): void
    {
        $dateTimeFolderName = date('Ymd_His');
        $random = rand(10000, 99999);
        $testDirectory = __DIR__."/../scratchpad/{$dateTimeFolderName}{$random}";
        $this->fileSystem->mkdir($testDirectory);
        $cwd = getcwd();
        $this->assertNotFalse($cwd);
        $this->projectRoot = new ProjectRoot($testDirectory, $cwd);
    }

    private function removeTestDirectory(): void
    {
        $this->fileSystem->remove((string) $this->projectRoot);
    }
}
