<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

class TextOutputFormatter implements OutputFormatter
{
    public function outputResults(AnalysisResults $analysisResults): string
    {
        if ($analysisResults->hasNoIssues()) {
            return 'No issues';
        }

        $output = '';

        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $location = $analysisResult->getLocation();

            $output .= <<<EOF
{$location->getFileName()->getFileName()}:{$location->getLineNumber()->getLineNumber()} - {$analysisResult->getType()->getType()}
{$analysisResult->getMessage()}


EOF;
        }

        return $output;
    }

    public function getIdentifier(): string
    {
        return 'text';
    }
}
