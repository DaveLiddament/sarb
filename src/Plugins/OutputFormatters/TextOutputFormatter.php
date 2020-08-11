<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\SummaryStats;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

class TextOutputFormatter implements OutputFormatter
{
    public function outputResults(SummaryStats $summaryStats, AnalysisResults $analysisResults): string
    {
        $output = <<<EOF
Latest issue count: {$summaryStats->getLatestAnalysisResultsCount()}
Baseline issue count: {$summaryStats->getBaseLineCount()}
Issues count with baseline removed: {$analysisResults->getCount()}
EOF;

        if ($analysisResults->hasNoIssues()) {
            return $output;
        }

        $output .= <<<EOF


--------


EOF;

        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $location = $analysisResult->getLocation();

            $output .= <<<EOF
{$location->getFileName()->getFileName()}:{$location->getLineNumber()->getLineNumber()} - {$analysisResult->getType()->getType()}
{$analysisResult->getMessage()}


EOF;
        }

        return $output;
    }

    public function getName(): string
    {
        return 'text';
    }
}
