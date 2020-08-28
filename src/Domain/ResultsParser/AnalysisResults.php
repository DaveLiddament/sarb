<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser;

/**
 * Holds all results from a run of the static analysis results.
 */
class AnalysisResults
{
    /**
     * @var AnalysisResult[]
     */
    private $analysisResults;

    public function __construct()
    {
        $this->analysisResults = [];
    }

    /**
     * @deprecated
     */
    public function addAnalysisResult(AnalysisResult $analysisResult): void
    {
        $this->analysisResults[] = $analysisResult;
    }

    /**
     * @return AnalysisResult[]
     */
    public function getAnalysisResults(): array
    {
        return $this->analysisResults;
    }

    /**
     * Ordered by Location.
     *
     * @return AnalysisResult[]
     */
    public function getOrderedAnalysisResults(): array
    {
        usort($this->analysisResults, function (AnalysisResult $a, AnalysisResult $b): int {
            return $a->getLocation()->compareTo($b->getLocation());
        });

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
}
