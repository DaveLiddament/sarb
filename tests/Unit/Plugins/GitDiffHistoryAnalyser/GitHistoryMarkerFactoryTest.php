<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitHistoryMarkerFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Assertions\AssertGitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser\internal\StubGitWrapper;
use PHPUnit\Framework\TestCase;

class GitHistoryMarkerFactoryTest extends TestCase
{
    use AssertGitCommit;

    /**
     * @var GitHistoryMarkerFactory
     */
    private $gitHistoryMarkerFactory;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $gitWrapper = new StubGitWrapper(StubGitWrapper::GIT_SHA_1, '');
        $this->gitHistoryMarkerFactory = new GitHistoryMarkerFactory($gitWrapper);
    }

    public function testNewHistoryMarker(): void
    {
        $actual = $this->gitHistoryMarkerFactory->newHistoryMarker(StubGitWrapper::GIT_SHA_2);
        $this->assertGitCommit(StubGitWrapper::GIT_SHA_2, $actual);
    }

    public function testNewCurrentHistoryMarker(): void
    {
        $actual = $this->gitHistoryMarkerFactory->newCurrentHistoryMarker(new ProjectRoot('/foo'));
        $this->assertGitCommit(StubGitWrapper::GIT_SHA_1, $actual);
        $this->assertInstanceOf(GitCommit::class, $actual);
    }
}
