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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;

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

    /**
     * Return as an array of arrays (ready for storing in a file).
     *
     * @phpstan-return array<mixed>
     *
     * @deprecated
     */
    public function asArray(): array
    {
        $array = [];
        foreach ($this->getAnalysisResults() as $analysisResult) {
            $array[] = $analysisResult->asArray();
        }

        return $array;
    }

    /**
     * Deserialises array representation to AnalysisResults.
     *
     * @throws ArrayParseException
     * @phpstan-param array<mixed> $array
     *
     * @return AnalysisResults
     *
     * @deprecated
     */
    public static function fromArray(array $array): self
    {
        $analysisResults = new self();

        /** @psalm-suppress MixedAssignment */
        foreach ($array as $entry) {
            ArrayUtils::assertArray($entry);
            $analysisResult = AnalysisResult::fromArray($entry);
            $analysisResults->addAnalysisResult($analysisResult);
        }

        return $analysisResults;
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
