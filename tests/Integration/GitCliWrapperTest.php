<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Integration;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\Parser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitCliWrapper;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final class GitCliWrapperTest extends TestCase
{
    use TestDirectoryTrait;

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
        $this->createTestDirectory();
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

    public function testDiffFormatNotAffectedByUserGitConfiguration(): void
    {
        // Git configuration options that alter the format of git diff output
        $this->setGitConfig('diff.noprefix', 'true');
        $this->setGitConfig('diff.mnemonicPrefix', 'true');
        $this->setGitConfig('core.quotePath', 'true');

        // Non ASCII filename (quoted by git diff when core.quotePath is true)
        $relativeFileName = new RelativeFileName('café.txt');
        $absoluteFileName = $this->projectRoot->getAbsoluteFileName($relativeFileName);

        $this->fileSystem->dumpFile($absoluteFileName->getFileName(), "line1\nline2\n");
        $this->gitWrapper->addAndCommit('Add file', $this->projectRoot);
        $gitCommit = $this->gitWrapper->getCurrentSha($this->projectRoot);

        $this->fileSystem->dumpFile($absoluteFileName->getFileName(), "line0\nline1\nline2\n");

        $diffAsString = $this->gitWrapper->getGitDiff($this->projectRoot, $gitCommit);
        $fileMutations = (new Parser())->parseDiff($diffAsString);

        Assert::assertNotNull(
            $fileMutations->getFileMutation(new NewFileName('café.txt')),
            "No FileMutation found. Diff was:\n$diffAsString",
        );
        $this->removeTestDirectory();
    }

    private function setGitConfig(string $key, string $value): void
    {
        $process = new Process(['git', "--git-dir={$this->projectRoot}/.git", 'config', $key, $value]);
        $process->run();
        Assert::assertTrue($process->isSuccessful(), $process->getErrorOutput());
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
}
