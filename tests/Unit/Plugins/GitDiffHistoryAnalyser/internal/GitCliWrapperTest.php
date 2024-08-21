<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitCliWrapper;
use PHPUnit\Framework\TestCase;

final class GitCliWrapperTest extends TestCase
{
    public function testGitDiffCommandFails(): void
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory(__DIR__);
        $gitCliWrapper = new GitCliWrapper();
        $nonExistantGitSha = new GitCommit('0000000000000000000000000000000000000000');
        $this->expectException(GitException::class);
        $gitCliWrapper->getGitDiff($projectRoot, $nonExistantGitSha);
    }

    public function testGetGitShaCommandFails(): void
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory(__DIR__);
        $gitCliWrapper = new GitCliWrapper();
        $this->expectException(GitException::class);
        $gitCliWrapper->getCurrentSha($projectRoot);
    }
}
