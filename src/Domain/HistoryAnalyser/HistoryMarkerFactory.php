<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;

interface HistoryMarkerFactory
{
    /**
     * Create HistoryMarker based on the string version of it.
     *
     * @param string $historyMarkerAsString
     *
     * @return HistoryMarker
     */
    public function newHistoryMarker(string $historyMarkerAsString): HistoryMarker;

    /**
     * Return HistoryMarker representing current state of the code.
     *
     * @param ProjectRoot $projectRoot
     *
     * @return HistoryMarker
     */
    public function newCurrentHistoryMarker(ProjectRoot $projectRoot): HistoryMarker;
}
