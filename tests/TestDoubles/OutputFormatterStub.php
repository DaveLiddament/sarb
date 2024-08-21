<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

final class OutputFormatterStub implements OutputFormatter
{
    public const CODE = 'stub';

    public function outputResults(AnalysisResults $analysisResults): string
    {
        return "[stub output formatter: Issues since baseline {$analysisResults->getCount()}]";
    }

    public function getIdentifier(): string
    {
        return self::CODE;
    }
}
