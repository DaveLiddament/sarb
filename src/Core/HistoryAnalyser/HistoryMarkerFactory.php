<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser;

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
     * @return HistoryMarker
     */
    public function newCurrentHistoryMarker(): HistoryMarker;
}
