<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Analyser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

/**
 * Checks if an AnalysisResult is in the baseline set of results.
 */
class BaseLineResultsComparator
{
    /**
     * @var AnalysisResults
     */
    private $baseLineResults;

    /**
     * BaseLineResultsComparator constructor.
     *
     * @param AnalysisResults $baseLineAnalysisResults
     */
    public function __construct(AnalysisResults $baseLineAnalysisResults)
    {
        $this->baseLineResults = $baseLineAnalysisResults;
    }

    /**
     * Returns true if an AnalysisResult of the same Type and Location exists in the BaseLine.
     *
     * @param Location $location
     * @param Type $type
     *
     * @return bool
     */
    public function isInBaseLine(Location $location, Type $type): bool
    {
        // Quite a simplistic approach maybe investigate performance for large results and, if needed, make a more
        // efficient searching mechanism

        foreach ($this->baseLineResults->getAnalysisResults() as $baseLineResult) {
            if ($baseLineResult->isMatch($location, $type)) {
                return true;
            }
        }

        return false;
    }
}
