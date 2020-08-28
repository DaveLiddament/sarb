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

    /**
     * @param AnalysisResult[] $analysisResults
     */
    public function __construct(array $analysisResults)
    {
        usort($analysisResults, function (AnalysisResult $a, AnalysisResult $b): int {
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
}
