<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Integration;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitCliWrapper;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class GitCliWrapperTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var GitCliWrapper
     */
    private $gitWrapper;
    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    protected function setUp(): void
    {
        $this->fileSystem = new Filesystem();
        $this->gitWrapper = new GitCliWrapper();

        $dateTimeFolderName = date('Ymd_His');
        $random = rand(0, 10000);
        $testDirectory = __DIR__."/../scratchpad/{$dateTimeFolderName}{$random}";
        $this->fileSystem->mkdir($testDirectory);
        $cwd = getcwd();
        $this->assertNotFalse($cwd);
        $this->projectRoot = new ProjectRoot($testDirectory, $cwd);
        $this->gitWrapper->init($this->projectRoot);
    }

    public function testNoChanges(): void
    {
        $this->assertClean();
    }

    public function testUntrackedFile(): void
    {
        $relativeFileName = new RelativeFileName('untracked.txt');
        $absoluteFileName = $this->projectRoot->getAbsoluteFileName($relativeFileName);
        $this->fileSystem->dumpFile($absoluteFileName->getFileName(), 'untracked');

        $this->assertNotClean();
    }

    public function testUpdatedFilesCommitted(): void
    {
        $relativeFileName = new RelativeFileName('committed.txt');
        $absoluteFileName = $this->projectRoot->getAbsoluteFileName($relativeFileName);

        // Add and commit file to git
        $this->fileSystem->dumpFile($absoluteFileName->getFileName(), 'committed');
        $this->gitWrapper->addAndCommit('Add file', $this->projectRoot);

        $this->assertClean();
    }

    public function testUnstaged(): void
    {
        $relativeFileName = new RelativeFileName('unstaged.txt');
        $absoluteFileName = $this->projectRoot->getAbsoluteFileName($relativeFileName);

        // Add and commit file to git
        $this->fileSystem->dumpFile($absoluteFileName->getFileName(), 'untracked');
        $this->gitWrapper->addAndCommit('Add file', $this->projectRoot);

        // Update
        $this->fileSystem->dumpFile($absoluteFileName->getFileName(), 'modified but not staged');

        $this->assertNotClean();
    }

    public function testStaged(): void
    {
        $relativeFileName = new RelativeFileName('staged.txt');
        $absoluteFileName = $this->projectRoot->getAbsoluteFileName($relativeFileName);

        // Add and commit file to git
        $this->fileSystem->dumpFile($absoluteFileName->getFileName(), 'untracked');
        $this->gitWrapper->addAndCommit('Add file', $this->projectRoot);

        // Update
        $this->fileSystem->dumpFile($absoluteFileName->getFileName(), 'staged');
        $this->gitWrapper->addAll($this->projectRoot);

        $this->assertNotClean();
    }

    public function assertNotClean(): void
    {
        Assert::assertFalse($this->gitWrapper->isClean($this->projectRoot));
        $this->removeTestDirectory();
    }

    private function assertClean(): void
    {
        Assert::assertTrue($this->gitWrapper->isClean($this->projectRoot));
        $this->removeTestDirectory();
    }

    private function removeTestDirectory(): void
    {
        $this->fileSystem->remove((string) $this->projectRoot);
    }
}
