<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Framework\Container;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\SummaryStats;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

class StubOutputFormatter1 implements OutputFormatter
{
    public const OUTPUT_FORMATTER_NAME = 'OUTPUT_FORMATTER_1';

    public function outputResults(SummaryStats $summaryStats, AnalysisResults $analysisResults): string
    {
        return '';
    }

    public function getIdentifier(): string
    {
        return self::OUTPUT_FORMATTER_NAME;
    }
}
