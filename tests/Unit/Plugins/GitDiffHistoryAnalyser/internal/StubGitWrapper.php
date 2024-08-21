<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitWrapper;

class StubGitWrapper implements GitWrapper
{
    public const GIT_SHA_1 = '683031e66625ba768350e5cb90d01121eae2ba00';
    public const GIT_SHA_2 = '0dcb42273e9deffb76997926d6748aa04487a75c';

    public function __construct(
        private string $sha,
        private string $diff,
    ) {
    }

    public function getCurrentSha(ProjectRoot $projectRoot): GitCommit
    {
        return new GitCommit($this->sha);
    }

    public function getGitDiff(ProjectRoot $projectRoot, GitCommit $originalCommit): string
    {
        return $this->diff;
    }

    public function isClean(ProjectRoot $projectRoot): bool
    {
        return true;
    }
}
