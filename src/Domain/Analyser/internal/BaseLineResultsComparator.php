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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineAnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineAnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\PreviousLocation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;

/**
 * Checks if an AnalysisResult is in the baseline set of results.
 */
class BaseLineResultsComparator
{
    /**
     * Stores base line results. With file name as key.
     *
     * @var array
     *
     * @psalm-var array<string, array<int,BaseLineAnalysisResult>>
     */
    private $baseLine;

    public function __construct(BaseLineAnalysisResults $baseLineAnalysisResults)
    {
        $this->baseLine = [];

        // For performance reasons put results into an array with the file name as the key
        foreach ($baseLineAnalysisResults->getBaseLineAnalysisResults() as $baseLineAnalysisResult) {
            $fileNameAsString = $baseLineAnalysisResult->getFileName()->getFileName();
            if (!array_key_exists($fileNameAsString, $this->baseLine)) {
                $this->baseLine[$fileNameAsString] = [];
            }
            $this->baseLine[$fileNameAsString][] = $baseLineAnalysisResult;
        }
    }

    /**
     * Returns true if an AnalysisResult of the same Type and Location exists in the BaseLine.
     */
    public function isInBaseLine(PreviousLocation $previousLocation, Type $type): bool
    {
        // Analysis result refers to a Location not in the BaseLine, then this is not an historic analysis result.
        if ($previousLocation->isNoPreviousLocation()) {
            return false;
        }

        // Now check through to history AnalysisResults to see if there is an exact match.
        // Check if file is in baseline
        $fileNameAsString = $previousLocation->getRelativeFileName()->getFileName();
        if (!array_key_exists($fileNameAsString, $this->baseLine)) {
            return false;
        }

        foreach ($this->baseLine[$fileNameAsString] as $baseLineResult) {
            if ($baseLineResult->isMatch($previousLocation, $type)) {
                return true;
            }
        }

        return false;
    }
}
