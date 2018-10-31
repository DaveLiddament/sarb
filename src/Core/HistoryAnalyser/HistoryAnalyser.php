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

use DaveLiddament\StaticAnalysisBaseliner\Core\Common\Location;
use DaveLiddament\StaticAnalysisBaseliner\Core\Common\PreviousLocation;

/**
 * Provides mappings between the current Location (file and line number) and where it in the baseline. (If anywhere).
 */
interface HistoryAnalyser
{
    /**
     * Return PreviousLocation (e.g. where it was in the baseline) for the current Location.
     *
     * @param Location $location
     *
     * @return PreviousLocation
     */
    public function getPreviousLocation(Location $location): PreviousLocation;
}
