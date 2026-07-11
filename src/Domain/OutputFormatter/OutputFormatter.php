<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

/**
 * Converts results into a output format required.
 */
interface OutputFormatter
{
    public function outputResults(AnalysisResults $analysisResults): string;

    public function getIdentifier(): string;
}
