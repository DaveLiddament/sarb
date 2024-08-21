<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryMarker;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryMarkerFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitWrapper;

final class GitHistoryMarkerFactory implements HistoryMarkerFactory
{
    /**
     * GitHistoryMarkerFactory constructor.
     */
    public function __construct(
        private GitWrapper $gitCliWrapper,
    ) {
    }

    public function newHistoryMarker(string $historyMarkerAsString): HistoryMarker
    {
        return new GitCommit($historyMarkerAsString);
    }

    public function newCurrentHistoryMarker(ProjectRoot $projectRoot, bool $forceBaselineCreation): HistoryMarker
    {
        // Create gitHistoryMarker first.
        // This will correctly deal with issues when $projectRoot is not pointing to a valid git repo.
        $gitHistoryMarker = $this->gitCliWrapper->getCurrentSha($projectRoot);

        // Return it if we are forcing baseline creation or the git repo is clean (i.e. no unchanged/new files)
        if ($forceBaselineCreation || $this->gitCliWrapper->isClean($projectRoot)) {
            return $gitHistoryMarker;
        }

        throw new GitNotCleanException();
    }
}
