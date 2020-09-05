<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitCliWrapper;
use PHPUnit\Framework\TestCase;

class GitCliWrapperTest extends TestCase
{
    public function testGitDiffCommandFails(): void
    {
        $projectRoot = new ProjectRoot(__DIR__, __DIR__);
        $gitCliWrapper = new GitCliWrapper();
        $this->expectException(GitException::class);
        $gitCliWrapper->getGitDiff($projectRoot, new GitCommit('8f98c8cb66cc85d80a9d57d30b73c49063360736'));
    }

    public function testGetGitShaCommandFails(): void
    {
        $projectRoot = new ProjectRoot(__DIR__, __DIR__);
        $gitCliWrapper = new GitCliWrapper();
        $this->expectException(GitException::class);
        $gitCliWrapper->getCurrentSha($projectRoot);
    }
}
