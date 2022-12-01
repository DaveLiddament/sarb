<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

class GithubActionsOutputFormatter implements OutputFormatter
{
    public function outputResults(
        AnalysisResults $analysisResults
    ): string {
        $lines = [];

        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $location = $analysisResult->getLocation();

            $message = str_replace("\n", '%0A', $analysisResult->getMessage());
            $lines[] = sprintf(
                '::%s file=%s,line=%d::%s',
                $analysisResult->getSeverity()->getSeverity(),
                $location->getRelativeFileName()->getFileName(),
                $location->getLineNumber()->getLineNumber(),
                $message,
            );
        }

        return implode(\PHP_EOL, $lines);
    }

    public function getIdentifier(): string
    {
        return 'github';
    }
}
