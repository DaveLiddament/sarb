<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Plugins\GitDiffHistoryAnalyser;

use DaveLiddament\StaticAnalysisBaseliner\Core\HistoryAnalyser\HistoryMarker;
use DaveLiddament\StaticAnalysisBaseliner\Core\HistoryAnalyser\HistoryMarkerFactory;
use DaveLiddament\StaticAnalysisBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitWrapper;

class GitHistoryMarkerFactory implements HistoryMarkerFactory
{
    /**
     * @var GitWrapper
     */
    private $gitCliWrapper;

    /**
     * GitHistoryMarkerFactory constructor.
     *
     * @param GitWrapper $gitCliWrapper
     */
    public function __construct(GitWrapper $gitCliWrapper)
    {
        $this->gitCliWrapper = $gitCliWrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function newHistoryMarker(string $historyMarkerAsString): HistoryMarker
    {
        return new GitCommit($historyMarkerAsString);
    }

    /**
     * {@inheritdoc}
     */
    public function newCurrentHistoryMarker(): HistoryMarker
    {
        return $this->gitCliWrapper->getCurrentSha();
    }
}
