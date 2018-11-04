<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitWrapper;

class StubGitWrapper implements GitWrapper
{
    public const GIT_SHA_1 = '683031e66625ba768350e5cb90d01121eae2ba00';
    public const GIT_SHA_2 = '0dcb42273e9deffb76997926d6748aa04487a75c';

    /**
     * @var string|null
     */
    private $projectRootDirectory;

    /**
     * @var string
     */
    private $sha;

    /**
     * @var string
     */
    private $diff;

    public function __construct(string $gitSha, string $diff)
    {
        $this->sha = $gitSha;
        $this->diff = $diff;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentSha(): GitCommit
    {
        return new GitCommit($this->sha);
    }

    /**
     * {@inheritdoc}
     */
    public function getGitDiff(GitCommit $originalCommit, GitCommit $newCommit): string
    {
        return $this->diff;
    }

    /**
     * {@inheritdoc}
     */
    public function setProjectRoot(string $projectRootDirectory): void
    {
        $this->projectRootDirectory = $projectRootDirectory;
    }

    /**
     * For testing purposes to check this has been called correctly.
     *
     * @return null|string
     */
    public function getProjectRootDirectory(): ?string
    {
        return $this->projectRootDirectory;
    }
}
