<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class SummaryStats
{
    /**
     * @var int
     */
    private $latestAnalysisResultsCount;
    /**
     * @var int
     */
    private $baseLineCount;
    /**
     * @var Identifier
     */
    private $resultsParser;
    /**
     * @var string
     */
    private $historyAnalyserName;

    public function __construct(
        int $latestAnalysisResultsCount,
        int $baseLineCount,
        Identifier $resultsParser,
        string $historyAnalyserName
    ) {
        $this->latestAnalysisResultsCount = $latestAnalysisResultsCount;
        $this->baseLineCount = $baseLineCount;
        $this->resultsParser = $resultsParser;
        $this->historyAnalyserName = $historyAnalyserName;
    }

    public function getLatestAnalysisResultsCount(): int
    {
        return $this->latestAnalysisResultsCount;
    }

    public function getBaseLineCount(): int
    {
        return $this->baseLineCount;
    }

    public function getResultsParser(): Identifier
    {
        return $this->resultsParser;
    }

    public function getHistoryAnalyserName(): string
    {
        return $this->historyAnalyserName;
    }
}
