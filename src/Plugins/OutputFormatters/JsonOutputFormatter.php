<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonUtils;

class JsonOutputFormatter implements OutputFormatter
{
    public function outputResults(
        AnalysisResults $baseLineRemovedResults
    ): string {
        $results = [];

        foreach ($baseLineRemovedResults->getAnalysisResults() as $analysisResult) {
            $location = $analysisResult->getLocation();
            $results[] = [
                'file' => $location->getFileName()->getFileName(),
                'line' => $location->getLineNumber()->getLineNumber(),
                'type' => $analysisResult->getType()->getType(),
                'message' => $analysisResult->getMessage(),
            ];
        }

        return JsonUtils::toString($results);
    }

    public function getIdentifier(): string
    {
        return 'json';
    }
}
