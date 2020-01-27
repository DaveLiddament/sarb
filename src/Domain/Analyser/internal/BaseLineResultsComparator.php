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
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

/**
 * Checks if an AnalysisResult is in the baseline set of results.
 */
class BaseLineResultsComparator
{
    /**
     * Stores base line results. With file name as key.
     *
     * @var array
     * @psalm-var array<string, array<mixed,AnalysisResult>>
     */
    private $baseLine;

    /**
     * BaseLineResultsComparator constructor.
     */
    public function __construct(AnalysisResults $baseLineAnalysisResults)
    {
        $this->baseLine = [];

        // For performance reasons put results into an array with the file name as the key
        foreach ($baseLineAnalysisResults->getAnalysisResults() as $baseLineAnalysisResult) {
            $fileNameAsString = $baseLineAnalysisResult->getLocation()->getFileName()->getFileName();
            if (!array_key_exists($fileNameAsString, $this->baseLine)) {
                $this->baseLine[$fileNameAsString] = [];
            }
            $this->baseLine[$fileNameAsString][] = $baseLineAnalysisResult;
        }
    }

    /**
     * Returns true if an AnalysisResult of the same Type and Location exists in the BaseLine.
     */
    public function isInBaseLine(Location $location, Type $type): bool
    {
        // Check if file is in baseline
        $fileNameAsString = $location->getFileName()->getFileName();
        if (!array_key_exists($fileNameAsString, $this->baseLine)) {
            return false;
        }

        foreach ($this->baseLine[$fileNameAsString] as $baseLineResult) {
            if ($baseLineResult->isMatch($location, $type)) {
                return true;
            }
        }

        return false;
    }
}
