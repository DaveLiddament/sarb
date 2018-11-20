<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;

interface GitWrapper
{
    /**
     * Returns a GitCommit representing the git HEAD or the project being analysed.
     *
     * @param ProjectRoot $projectRoot
     *
     * @return GitCommit
     */
    public function getCurrentSha(ProjectRoot $projectRoot): GitCommit;

    /**
     * Returns a diff (as a string) between the 2 commits.
     *
     * @param ProjectRoot $projectRoot
     * @param GitCommit $originalCommit
     * @param GitCommit $newCommit
     *
     * @return string
     */
    public function getGitDiff(ProjectRoot $projectRoot, GitCommit $originalCommit, GitCommit $newCommit): string;
}
