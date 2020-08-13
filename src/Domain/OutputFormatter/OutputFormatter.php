<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

/**
 * Converts results into a output format required.
 */
interface OutputFormatter
{
    public function outputResults(
        SummaryStats $summaryStats,
        AnalysisResults $analysisResults
    ): string;

    public function getIdentifier(): string;
}
