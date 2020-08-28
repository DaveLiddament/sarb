<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;

class BaseLineAnalysisResults
{
    /**
     * @var BaseLineAnalysisResult[]
     */
    private $baseLineAnalysisResults;

    /**
     * @phpstan-param array<mixed> $array
     *
     * @throws ParseAtLocationException
     */
    public static function fromArray(array $array): self
    {
        $baseLineAnalysisResults = [];

        $resultCount = 0;
        /** @psalm-suppress MixedAssignment */
        foreach ($array as $entry) {
            ++$resultCount;
            try {
                ArrayUtils::assertArray($entry);
                $baseLineAnalysisResult = BaseLineAnalysisResult::fromArray($entry);
                $baseLineAnalysisResults[] = $baseLineAnalysisResult;
            } catch (ArrayParseException $e) {
                throw ParseAtLocationException::issueAtPosition($e, $resultCount);
            }
        }

        return new self($baseLineAnalysisResults);
    }

    public static function fromAnalysisResults(AnalysisResults $analysisResults): self
    {
        $baseLineAnalysisResults = [];
        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $baseLineAnalysisResults[] = $analysisResult->asBaseLineAnalysisResult();
        }

        return new self($baseLineAnalysisResults);
    }

    /**
     * @param BaseLineAnalysisResult[] $baseLineAnalysisResults
     */
    private function __construct(array $baseLineAnalysisResults)
    {
        $this->baseLineAnalysisResults = $baseLineAnalysisResults;
    }

    public function getCount(): int
    {
        return count($this->baseLineAnalysisResults);
    }

    /**
     * @return BaseLineAnalysisResult[]
     */
    public function getBaseLineAnalysisResults(): array
    {
        return $this->baseLineAnalysisResults;
    }

    /**
     * Return as an array of arrays (ready for storing in a file).
     *
     * @phpstan-return array<int, array<string,int|string>>
     */
    public function asArray(): array
    {
        $array = [];
        foreach ($this->getBaseLineAnalysisResults() as $baseLineAnalysisResult) {
            $array[] = $baseLineAnalysisResult->asArray();
        }

        return $array;
    }
}
