<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Core\HistoryAnalyser;

/**
 * Representation of the point in history the code base is at. Build using a HistoryMarkerFactory.
 *
 * (E.g. if using git this would be the git commit)
 */
interface HistoryMarker
{
    /**
     * Return the history maker as a string (to be stored in BaseLine).
     *
     * @return string
     */
    public function asString(): string;
}
