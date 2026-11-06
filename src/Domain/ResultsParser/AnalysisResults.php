<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\TypeIdentifiersUsage;

/**
 * Holds all results from a run of the static analysis results.
 */
final class AnalysisResults
{
    /**
     * @var AnalysisResult[]
     */
    private $analysisResults;

    /**
     * @param AnalysisResult[] $analysisResults
     */
    public function __construct(array $analysisResults)
    {
        usort($analysisResults, static function (AnalysisResult $a, AnalysisResult $b): int {
            return $a->getLocation()->compareTo($b->getLocation());
        });

        $this->analysisResults = $analysisResults;
    }

    /**
     * Returns array of AnalysisResult objects, ordered by file name and then line number.
     *
     * @return AnalysisResult[]
     */
    public function getAnalysisResults(): array
    {
        return $this->analysisResults;
    }

    public function getCount(): int
    {
        return count($this->analysisResults);
    }

    public function hasNoIssues(): bool
    {
        return 0 === $this->getCount();
    }

    public function getTypeIdentifiersUsage(): TypeIdentifiersUsage
    {
        $resultsWithTypeIdentifierCount = 0;
        foreach ($this->analysisResults as $analysisResult) {
            if (null !== $analysisResult->getLegacyType()) {
                ++$resultsWithTypeIdentifierCount;
            }
        }

        if (0 === $resultsWithTypeIdentifierCount) {
            return TypeIdentifiersUsage::none();
        }

        if ($resultsWithTypeIdentifierCount === $this->getCount()) {
            return TypeIdentifiersUsage::all();
        }

        return TypeIdentifiersUsage::some();
    }
}
