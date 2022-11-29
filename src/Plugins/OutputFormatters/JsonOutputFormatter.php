<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonUtils;

class JsonOutputFormatter implements OutputFormatter
{
    public function outputResults(
        AnalysisResults $analysisResults
    ): string {
        $results = [];

        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $location = $analysisResult->getLocation();
            $results[] = [
                'file' => $location->getAbsoluteFileName()->getFileName(),
                'line' => $location->getLineNumber()->getLineNumber(),
                'type' => $analysisResult->getType()->getType(),
                'message' => $analysisResult->getMessage(),
                'severity' => $analysisResult->getSeverity()->getSeverity(),
            ];
        }

        return JsonUtils::toString($results);
    }

    public function getIdentifier(): string
    {
        return 'json';
    }
}
