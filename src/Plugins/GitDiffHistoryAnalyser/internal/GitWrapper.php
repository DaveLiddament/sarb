<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Plugins\GitDiffHistoryAnalyser\internal;

use DaveLiddament\StaticAnalysisBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;

interface GitWrapper
{
    /**
     * Returns a GitCommit representing the git HEAD or the project being analysed.
     *
     * @return GitCommit
     */
    public function getCurrentSha(): GitCommit;

    /**
     * Returns a diff (as a string) between the 2 commits.
     *
     * @param GitCommit $originalCommit
     * @param GitCommit $newCommit
     *
     * @return string
     */
    public function getGitDiff(GitCommit $originalCommit, GitCommit $newCommit): string;

    /**
     * Set path to root directory of project being analysed.
     *
     * @param string $projectRootDirectory
     */
    public function setProjectRoot(string $projectRootDirectory): void;
}
