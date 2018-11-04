<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\Parser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\DiffHistoryAnalyser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitDiffHistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Assertions\AssertGitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser\internal\StubGitWrapper;
use PHPUnit\Framework\TestCase;

class GitDiffHistoryFactoryTest extends TestCase
{
    use AssertGitCommit;

    /**
     * @var string
     */
    private const PROJECT_ROOT = 'project/foo';

    /**
     * @var GitDiffHistoryFactory
     */
    private $gitDiffHistoryFactory;

    /**
     * @var StubGitWrapper
     */
    private $gitWrapper;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->gitWrapper = new StubGitWrapper(StubGitWrapper::GIT_SHA_1, '');
        $parser = new Parser();
        $this->gitDiffHistoryFactory = new GitDiffHistoryFactory($this->gitWrapper, $parser);
    }

    public function testNewHistoryAnalyser(): void
    {
        $gitCommit = new GitCommit(StubGitWrapper::GIT_SHA_2);
        $diffHistoryAnalyser = $this->gitDiffHistoryFactory->newHistoryAnalyser($gitCommit);
        $this->assertInstanceOf(DiffHistoryAnalyser::class, $diffHistoryAnalyser);
    }

    public function testGetIdentifer(): void
    {
        $this->assertSame('git', $this->gitDiffHistoryFactory->getIdentifier());
    }

    public function testNewHistoryMarkerFactory(): void
    {
        $historyMarkerFactory = $this->gitDiffHistoryFactory->newHistoryMarkerFactory();

        // To check everything is setup correctly we'll call the newCurrentHitsoryMarker
        // (SHA set in setup method via the StubGitWrapper)
        $currentCommit = $historyMarkerFactory->newCurrentHistoryMarker();
        $this->assertGitCommit(StubGitWrapper::GIT_SHA_1, $currentCommit);
    }

    public function testSetProjectRoot(): void
    {
        $this->assertNull($this->gitWrapper->getProjectRootDirectory());
        $this->gitDiffHistoryFactory->setProjectRoot(self::PROJECT_ROOT);
        $this->assertSame(self::PROJECT_ROOT, $this->gitWrapper->getProjectRootDirectory());
    }
}
