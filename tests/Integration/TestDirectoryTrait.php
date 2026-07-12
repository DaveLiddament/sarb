<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Integration;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;

trait TestDirectoryTrait
{
    private bool $testDirectoryCreated = false;

    private function createTestDirectory(): void
    {
        $dateTimeFolderName = date('Ymd_His');
        $random = random_int(10000, 99999);
        $testDirectory = __DIR__."/../scratchpad/{$dateTimeFolderName}{$random}";
        $this->fileSystem->mkdir($testDirectory);
        $cwd = getcwd();
        $this->assertNotFalse($cwd);
        $this->projectRoot = ProjectRoot::fromProjectRoot($testDirectory, $cwd);
        $this->testDirectoryCreated = true;
    }

    /**
     * Removes the test directory if the test passed.
     * Kept when the test failed, to help investigate the failure.
     */
    protected function tearDown(): void
    {
        if ($this->testDirectoryCreated && $this->status()->isSuccess()) {
            $this->removeTestDirectory();
        }
    }

    private function removeTestDirectory(): void
    {
        $this->fileSystem->remove((string) $this->projectRoot);
    }
}
